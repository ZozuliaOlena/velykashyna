<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Livewire\Concerns\WithAdminToast;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class UserIndex extends Component
{
    use WithPagination;
    use WithAdminToast;

    public string $search = '';
    public bool $showModal = false;
    public ?int $editingId = null;

    public string $name = '';
    public string $email = '';
    public string $role = 'manager';
    public string $password = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role'  => ['required', 'in:admin,manager'],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function openCreate(): void
    {
        $this->reset('name', 'email', 'role', 'password', 'editingId');
        $this->role = 'manager';
        $this->showModal = true;
    }

    public function openEdit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $id;
        $this->name  = $user->name;
        $this->email = $user->email;
        $this->role  = $user->role;
        $this->password = '';
        $this->showModal = true;
    }

    public function save(): void
    {
        $data = $this->validate();

        $payload = [
            'name'  => $data['name'],
            'email' => $data['email'],
            'role'  => $data['role'],
        ];

        if (! empty($data['password'])) {
            $payload['password'] = Hash::make($data['password']);
        }

        if ($this->editingId) {
            User::findOrFail($this->editingId)->update($payload);
        } else {
            $payload['email_verified_at'] = now();
            User::create($payload);
        }

        $this->showModal = false;
        $this->reset('name', 'email', 'role', 'password', 'editingId');
        session()->flash('success', 'Збережено');
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('error', 'Не можна видалити власний обліковий запис');
            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('success', 'Видалено');
    }

    public function render()
    {
        $users = User::query()
            ->when($this->search, fn ($q) => $q->where('name', 'like', "%{$this->search}%")
                ->orWhere('email', 'like', "%{$this->search}%"))
            ->orderBy('name')
            ->paginate(20);

        return view('admin.users.user-index', compact('users'))
            ->layout('admin.layouts.admin');
    }
}
