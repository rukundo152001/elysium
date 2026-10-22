<x-admin-layout :title="'Staff Management'">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">Manage Users</h1>
    <a href="{{ route('admin.users.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md shadow hover:bg-blue-700">New User</a>
  </div>

  <div class="bg-white shadow rounded-lg p-4">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between mb-4">
      <form method="GET" action="{{ route('admin.users.index') }}" class="flex items-center gap-2 w-full md:w-1/2">
        <input type="text" name="q" value="{{ request('q') }}" placeholder="Search by name, email or nid" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring" />
        <button class="px-4 py-2 bg-gray-800 text-white rounded-md">Search</button>
      </form>
      <div class="mt-3 md:mt-0">
        <span class="text-sm text-gray-600">Showing {{ $users->firstItem() ?? 0 }} - {{ $users->lastItem() ?? 0 }} of {{ $users->total() }} users</span>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead>
          <tr class="text-left text-sm uppercase text-gray-600">
            <th class="px-4 py-2">Name</th>
            <th class="px-4 py-2">Email</th>
            <th class="px-4 py-2">NID</th>
            <th class="px-4 py-2">Role</th>
            <th class="px-4 py-2">Status</th>
            <th class="px-4 py-2">Actions</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-100">
          @forelse($users as $user)
          <tr class="hover:bg-gray-50">
            <td class="px-4 py-3">{{ $user->name }}</td>
            <td class="px-4 py-3">{{ $user->email }}</td>
            <td class="px-4 py-3">{{ $user->nid }}</td>
            <td class="px-4 py-3">{{ ucfirst($user->role) }}</td>
            <td class="px-4 py-3">{{ ucfirst($user->status) }}</td>
            <td class="px-4 py-3">
              <div class="flex items-center gap-2">
                <a href="{{ route('admin.users.show', $user->id) }}" class="text-sm px-2 py-1 rounded border">View</a>
                <a href="{{ route('admin.users.edit', $user->id) }}" class="text-sm px-2 py-1 rounded border">Edit</a>
                <button @click="confirmDelete({{ $user->id }}, '{{ addslashes($user->name) }}')" class="text-sm px-2 py-1 rounded border text-red-600">Delete</button>
              </div>
            </td>
          </tr>
          @empty
          <tr>
            <td colspan="6" class="p-6 text-center text-gray-500">No users found.</td>
          </tr>
          @endforelse
        </tbody>
      </table>
    </div>

    <div class="mt-4">
      {{ $users->withQueryString()->links() }}
    </div>
  </div>

  <!-- Delete Modal (Alpine) -->
  <div x-data="userIndex()" x-init="() => {}">
    <div x-show="showModal" class="fixed inset-0 z-40 flex items-center justify-center" style="background: rgba(0,0,0,0.4)">
      <div class="bg-white rounded-lg shadow p-6 w-full max-w-md" @click.away="showModal = false">
        <h2 class="text-lg font-semibold mb-2">Delete user</h2>
        <p class="text-sm text-gray-600 mb-4">Are you sure you want to delete <span class="font-medium" x-text="selectedName"></span>? This action cannot be undone.</p>
        <div class="flex justify-end gap-3">
          <button @click="showModal = false" class="px-4 py-2 rounded border">Cancel</button>
          <form :action="deleteUrl" method="POST">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-4 py-2 rounded bg-red-600 text-white">Delete</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>


<script>
  function userIndex(){
    return {
      showModal: false,
      selectedId: null,
      selectedName: '',
      get deleteUrl(){
        return `/admin/users/${this.selectedId}`
      },
      confirmDelete(id, name){
        this.selectedId = id;
        this.selectedName = name;
        this.showModal = true;
      }
    }
  }
</script>
</x-admin-layout>
