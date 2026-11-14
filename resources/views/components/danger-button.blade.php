<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-2.5 bg-rose-600 border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest hover:bg-rose-500 hover:shadow-lg hover:-translate-y-0.5 active:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2 dark:focus:ring-offset-slate-900 transition-all duration-300']) }}>
    {{ $slot }}
</button>
