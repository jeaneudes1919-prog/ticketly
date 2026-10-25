<div x-data="{ names: [] }">
    <label class="flex cursor-pointer flex-col items-center gap-1 rounded-xl border-2 border-dashed border-slate-300 px-4 py-5 text-center text-sm text-slate-500 transition hover:border-blue-500 hover:bg-blue-50/50 dark:border-slate-700 dark:hover:bg-blue-500/5">
        <x-icon n="clip" class="h-6 w-6"/><span><b class="text-blue-600">Ajouter des fichiers</b> (5 max, 5 Mo chacun : images, PDF, DOCX, ZIP)</span>
        <input type="file" name="files[]" multiple class="hidden" @change="names = [...$event.target.files].map(f => f.name)">
    </label>
    <ul class="mt-2 flex flex-wrap gap-2" x-show="names.length"><template x-for="n in names"><li class="badge bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-300" x-text="n"></li></template></ul>
    @error('files.*')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
</div>
