<button {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full rounded-full bg-lime-400 py-2.5 text-sm font-semibold text-gray-900 transition hover:bg-lime-500 focus:outline-none focus:ring-2 focus:ring-lime-500 focus:ring-offset-2']) }}>
    {{ $slot }}
</button>
