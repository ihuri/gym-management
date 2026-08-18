<div class="space-y-3">
    @if($students->isEmpty())
        <p class="text-sm text-gray-500 dark:text-gray-400">Nenhum aluno matriculado para este horário.</p>
    @else
        <div class="divide-y divide-gray-200 dark:divide-gray-800 rounded-lg border border-gray-200 dark:border-gray-800">
            @foreach($students as $student)
                <div class="flex items-center justify-between p-3">
                    <div class="flex items-center gap-3">
                        @if($student->photo_path)
                            <img src="{{ Storage::url($student->photo_path) }}" alt="{{ $student->name }}" class="h-8 w-8 rounded-full object-cover">
                        @else
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-primary-100 text-primary-700 dark:bg-primary-950 dark:text-primary-300 text-xs font-bold">
                                {{ strtoupper(substr($student->name, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $student->name }}</p>
                            <p class="text-xs text-gray-500 dark:text-gray-400">{{ $student->phone }} | CPF: {{ $student->cpf }}</p>
                        </div>
                    </div>
                    <div>
                        <span class="inline-flex items-center rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-700 ring-1 ring-inset ring-emerald-600/20 dark:bg-emerald-950 dark:text-emerald-300">
                            {{ ucfirst($student->pivot->status ?? 'ativo') }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
