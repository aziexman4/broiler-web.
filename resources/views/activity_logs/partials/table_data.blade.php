                    <div class="max-h-[500px] overflow-y-auto overflow-x-auto relative shadow-md sm:rounded-xl border border-slate-200">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Waktu
                                    </th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Pengguna
                                    </th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Modul
                                    </th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Deskripsi
                                    </th>
                                    <th scope="col" class="sticky top-0 bg-slate-100 z-10 border-b border-slate-200 px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                                        Detail Perubahan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @forelse ($logs as $log)
                                    <tr class="hover:bg-slate-50 transition">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                            {{ $log->created_at->format('d M Y H:i:s') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-900">
                                            {{ $log->user->name ?? 'Sistem' }}
                                            <div class="text-xs text-slate-500">{{ $log->user->role ?? '' }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-700">
                                            {{ $log->model_type }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($log->action === 'created')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                                    Ditambahkan
                                                </span>
                                            @elseif($log->action === 'updated')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                                    Diperbarui
                                                </span>
                                            @elseif($log->action === 'deleted')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                                    Dihapus
                                                </span>
                                            @else
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-slate-100 text-slate-800">
                                                    {{ ucfirst($log->action) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-700 max-w-xs truncate" title="{{ $log->description }}">
                                            {{ $log->description }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-slate-500">
                                            @if($log->changes)
                                                <div x-data="{ open: false }" class="relative">
                                                    <button @click="open = !open" type="button" class="text-indigo-600 hover:text-indigo-900 text-xs font-medium underline">
                                                        Lihat Perubahan
                                                    </button>
                                                    <div x-show="open" @click.away="open = false" x-cloak class="mt-2 text-xs bg-slate-50 p-2 rounded border border-slate-200 w-64 absolute z-10 shadow-lg left-0">
                                                        <div class="mb-1 font-semibold text-red-600">Sebelumnya:</div>
                                                        <pre class="overflow-x-auto text-[10px]">{{ json_encode(array_intersect_key($log->changes['before'] ?? [], $log->changes['after'] ?? []), JSON_PRETTY_PRINT) }}</pre>
                                                        <div class="mt-2 mb-1 font-semibold text-green-600">Sesudahnya:</div>
                                                        <pre class="overflow-x-auto text-[10px]">{{ json_encode($log->changes['after'] ?? [], JSON_PRETTY_PRINT) }}</pre>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-slate-400 italic text-xs">Tidak ada detail</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 text-center">
                                            Belum ada log aktivitas yang tercatat.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4 ajax-pagination">
                        {{ $logs->links() }}
                    </div>
