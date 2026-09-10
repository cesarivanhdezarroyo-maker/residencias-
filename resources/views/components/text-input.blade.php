@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm']) }}>
