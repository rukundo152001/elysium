<x-admin-layout>

<div class="max-w-3xl mx-auto py-8">
  <div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-semibold">User Details</h1>
    <div class="flex items-center gap-2">
      <a href="{{ route('admin.users.edit', $user->id) }}" class="px-3 py-1 border rounded">Edit</a>
      <a href="{{ route('admin.users.index') }}" class="px-3 py-1 border rounded">Back</a>
    </div>
  </div>

  <div class="bg-white shadow rounded-lg p-6">
    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
      <div>
        <dt class="text-sm text-gray-500">Name</dt>
        <dd class="mt-1 font-medium">{{ $user->name }}</dd>
      </div>

      <div>
        <dt class="text-sm text-gray-500">Email</dt>
        <dd class="mt-1 font-medium">{{ $user->email }}</dd>
      </div>

      <div>
        <dt class="text-sm text-gray-500">NID</dt>
        <dd class="mt-1 font-medium">{{ $user->nid }}</dd>
      </div>

      <div>
        <dt class="text-sm text-gray-500">Phone</dt>
        <dd class="mt-1 font-medium">{{ $user->phone ?? '—' }}</dd>
      </div>

      <div>
        <dt class="text-sm text-gray-500">Role</dt>
        <dd class="mt-1 font-medium">{{ ucfirst($user->role) }}</dd>
      </div>

      <div>
        <dt class="text-sm text-gray-500">Status</dt>
        <dd class="mt-1 font-medium">{{ ucfirst($user->status) }}</dd>
      </div>

      <div class="md:col-span-2">
        <dt class="text-sm text-gray-500">Created at</dt>
        <dd class="mt-1 text-sm text-gray-600">
          {{ $user->created_at->format('F j, Y, g:ia') }}
        </dd>
      </div>
    </dl>
  </div>
</div>

</x-admin-layout>
