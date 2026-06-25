<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Support\Translit;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class Category extends Model
{
    use SoftDeletes, HasSlug;

    protected $fillable = [
        'parent_id', 'level', 'name', 'slug',
        'seo_title', 'seo_description', 'seo_h1',
        'image', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom(fn (self $model) => Translit::uk($model->name))
            ->saveSlugsTo('slug');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    /**
     * Категорії, відсортовані обходом дерева: кожна йде одразу під своїм
     * батьком, у межах одного батька — за sort_order, потім name.
     * Завдяки цьому відступ за рівнем (level) стає змістовним і в таблиці,
     * і у випадних списках. $cb дозволяє доповнити запит (напр. withCount).
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function treeOrdered(?\Closure $cb = null): \Illuminate\Support\Collection
    {
        $query = static::query()->orderBy('sort_order')->orderBy('name');
        if ($cb) {
            $cb($query);
        }

        $byParent = [];
        foreach ($query->get() as $cat) {
            $byParent[$cat->parent_id ?? 0][] = $cat;
        }

        $ordered = collect();
        // Псевдографіка дерева (├─ └─ │) для випадних списків.
        // Неразривні пробіли (\u{00A0}), щоб браузер не схлопував відступ в <option>.
        $nb = "\u{00A0}";
        $walk = function ($parentId, $ancestorPrefix) use (&$walk, &$byParent, $ordered, $nb) {
            $siblings = $byParent[$parentId] ?? [];
            $last = count($siblings) - 1;

            foreach ($siblings as $i => $node) {
                $isLast = $i === $last;

                // «Гілка» — категорія, що має дітей (виділяється як заголовок).
                $node->is_branch = ! empty($byParent[$node->id]);

                if ($node->parent_id === null) {
                    $node->tree_prefix = '';      // корінь — без префікса (буде жирним)
                    $childPrefix = '';
                } else {
                    $node->tree_prefix = $ancestorPrefix . ($isLast ? '└─' . $nb : '├─' . $nb);
                    $childPrefix = $ancestorPrefix . ($isLast ? $nb . $nb . $nb : '│' . $nb . $nb);
                }

                $ordered->push($node);
                $walk($node->id, $childPrefix);
            }
        };
        $walk(0, '');

        return $ordered;
    }

    /**
     * Вкладене дерево: повертає кореневі вузли, у кожного встановлено
     * relation `children` (рекурсивно), відсортований за sort_order, name.
     * Зручно для рекурсивного рендера згортуваного дерева.
     *
     * @return \Illuminate\Support\Collection<int, static>
     */
    public static function nestedTree(?\Closure $cb = null): \Illuminate\Support\Collection
    {
        $query = static::query()->orderBy('sort_order')->orderBy('name');
        if ($cb) {
            $cb($query);
        }

        $byParent = [];
        foreach ($query->get() as $cat) {
            $byParent[$cat->parent_id ?? 0][] = $cat;
        }

        $attach = function ($nodes) use (&$attach, &$byParent) {
            foreach ($nodes as $node) {
                $kids = collect($byParent[$node->id] ?? []);
                $attach($kids);
                $node->setRelation('children', $kids);
            }
        };

        $roots = collect($byParent[0] ?? []);
        $attach($roots);

        return $roots;
    }
}
