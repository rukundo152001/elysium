<x-admin-layout>
<div class="max-w-3xl mx-auto py-8">
  <h1 class="text-2xl font-semibold mb-6">Create User</h1>

  <div class="bg-white shadow rounded-lg p-6">
    <form method="POST" action="{{ route('admin.users.store') }}" x-data="userForm()" @submit="onSubmit">
      @csrf

      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-medium">Name</label>
          <input type="text" name="name" x-model="form.name" value="{{ old('name') }}" class="mt-1 block w-full rounded-md border px-3 py-2" required>
          @error('name')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Email</label>
          <input type="email" name="email" x-model="form.email" value="{{ old('email') }}" class="mt-1 block w-full rounded-md border px-3 py-2" required>
          @error('email')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium">NID</label>
          <input type="text" name="nid" x-model="form.nid" value="{{ old('nid') }}" class="mt-1 block w-full rounded-md border px-3 py-2" required>
          @error('nid')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Phone</label>
          <input type="text" name="phone" x-model="form.phone" value="{{ old('phone') }}" class="mt-1 block w-full rounded-md border px-3 py-2">
          @error('phone')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Role</label>
          <select name="role" x-model="form.role" class="mt-1 block w-full rounded-md border px-3 py-2">
            <option value="staff">Staff</option>
            <option value="admin">Admin</option>
            <option value="cashier">Cashier</option>
            <option value="technician">Technician</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium">Status</label>
          <select name="status" x-model="form.status" class="mt-1 block w-full rounded-md border px-3 py-2">
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>

        <div>
          <label class="block text-sm font-medium">Password</label>
          <input type="password" name="password" class="mt-1 block w-full rounded-md border px-3 py-2" required>
          @error('password')<p class="text-sm text-red-600 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
          <label class="block text-sm font-medium">Confirm Password</label>
          <input type="password" name="password_confirmation" class="mt-1 block w-full rounded-md border px-3 py-2" required>
        </div>
      </div>

      <div class="mt-6 flex items-center gap-3">
        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 border rounded">Cancel</a>
        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded">Create</button>
      </div>
    </form>
  </div>
</div>


<script>
  function userForm(){
    return {
      form: {
        name: '', email: '', nid: '', phone: '', role: 'staff', status: 'active'
      },
      onSubmit(e){
        // Optionally do front-end validation here
      }
    }
  }
</script>
</x-admin-layout>