@props([
    'name',
    'options' => [],          // [valor => texto]
    'selected' => null,       // valor preseleccionado
    'placeholder' => 'Escribe para buscar...',
    'required' => false,
])

@php
    $id = 'buscador_' . $name;
    $pares = collect($options)->map(fn ($texto, $valor) => [(string) $valor, (string) $texto])->values();
    $valorInicial = ($selected !== null && $selected !== '' && array_key_exists($selected, $options)) ? (string) $selected : '';
    $textoInicial = $valorInicial !== '' ? (string) $options[$selected] : '';
@endphp

<div id="{{ $id }}_raiz" class="relative">
    <input type="text" id="{{ $id }}_texto" autocomplete="off" spellcheck="false"
           value="{{ $textoInicial }}" placeholder="{{ $placeholder }}"
           class="mt-1 block w-full border-gray-300 focus:border-[#621132] focus:ring-[#621132] rounded-md shadow-sm text-sm">
    <input type="hidden" name="{{ $name }}" id="{{ $id }}_valor" value="{{ $valorInicial }}">
    <ul id="{{ $id }}_lista" class="hidden absolute z-30 mt-1 w-full max-h-64 overflow-auto bg-white border border-gray-300 rounded-md shadow-lg text-sm"></ul>
    <script type="application/json" id="{{ $id }}_datos">@json($pares, JSON_HEX_TAG | JSON_HEX_AMP)</script>
</div>

<script>
(function () {
    var datos = JSON.parse(document.getElementById('{{ $id }}_datos').textContent);
    var entrada = document.getElementById('{{ $id }}_texto');
    var oculto = document.getElementById('{{ $id }}_valor');
    var lista = document.getElementById('{{ $id }}_lista');
    var obligatorio = {{ $required ? 'true' : 'false' }};
    var MENSAJE = 'Selecciona una opción de la lista.';
    var activo = -1;
    var mostrados = [];

    // Sin acentos, en minúsculas y solo letras, números y espacios
    function limpiar(s) {
        return s.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase()
                .replace(/[^a-z0-9 ]+/g, ' ').replace(/\s+/g, ' ').trim();
    }
    function bigramas(s) {
        var r = {}, n = 0;
        if (s.length < 2) { r[s] = 1; return { set: r, n: 1 }; }
        for (var i = 0; i < s.length - 1; i++) { r[s.substr(i, 2)] = (r[s.substr(i, 2)] || 0) + 1; n++; }
        return { set: r, n: n };
    }
    function dice(a, b) {
        var comunes = 0;
        for (var k in a.set) { if (b.set[k]) { comunes += Math.min(a.set[k], b.set[k]); } }
        return (2 * comunes) / (a.n + b.n);
    }

    var items = datos.map(function (par) {
        var norm = limpiar(par[1]);
        var tokens = norm.split(' ');
        return { valor: par[0], texto: par[1], norm: norm, tokens: tokens, tokBig: tokens.map(bigramas) };
    });

    // Puntaje: coincidencia exacta > empieza con > contiene > todas las palabras > parecido (tolera errores)
    function puntuar(q, qTokens, qBig, item) {
        var t = item.norm;
        if (t === q) { return 1000; }
        if (t.indexOf(q) === 0) { return 900 - t.length / 1000; }
        var pos = t.indexOf(q);
        if (pos > 0) { return 800 - pos / 100; }

        var todas = qTokens.every(function (w) { return t.indexOf(w) >= 0; });
        if (todas) { return 700 - t.length / 1000; }

        var suma = 0;
        for (var i = 0; i < qTokens.length; i++) {
            var mejor = 0;
            for (var j = 0; j < item.tokens.length; j++) {
                var s = item.tokens[j].indexOf(qTokens[i]) === 0 ? 1 : dice(qBig[i], item.tokBig[j]);
                if (s > mejor) { mejor = s; }
            }
            suma += mejor;
        }
        var prom = suma / qTokens.length;
        return prom >= 0.6 ? 100 + prom * 100 : 0;
    }

    function buscar(texto) {
        var q = limpiar(texto);
        if (q === '') { return items.slice(0, 12); }
        var qTokens = q.split(' ');
        var qBig = qTokens.map(bigramas);
        var res = [];
        for (var i = 0; i < items.length; i++) {
            var p = puntuar(q, qTokens, qBig, items[i]);
            if (p > 0) { res.push({ it: items[i], p: p }); }
        }
        res.sort(function (a, b) { return b.p - a.p; });
        return res.slice(0, 12).map(function (r) { return r.it; });
    }

    function validar() {
        entrada.setCustomValidity(obligatorio && oculto.value === '' ? MENSAJE : '');
    }

    function pintar() {
        lista.innerHTML = '';
        if (mostrados.length === 0) {
            var vacio = document.createElement('li');
            vacio.className = 'px-3 py-2 text-gray-500';
            vacio.textContent = 'Sin coincidencias';
            lista.appendChild(vacio);
        }
        mostrados.forEach(function (it, i) {
            var li = document.createElement('li');
            li.textContent = it.texto;
            li.className = 'px-3 py-2 cursor-pointer ' + (i === activo ? 'bg-[#fdf2f6] text-[#621132] font-bold' : 'hover:bg-gray-100');
            li.addEventListener('mousedown', function (e) { e.preventDefault(); elegir(it); });
            lista.appendChild(li);
        });
        lista.classList.remove('hidden');
    }

    function elegir(it) {
        entrada.value = it.texto;
        oculto.value = it.valor;
        lista.classList.add('hidden');
        validar();
    }

    function abrir() {
        mostrados = buscar(entrada.value);
        activo = mostrados.length ? 0 : -1;
        pintar();
    }

    entrada.addEventListener('input', function () { oculto.value = ''; validar(); abrir(); });
    entrada.addEventListener('focus', function () { entrada.select(); abrir(); });
    entrada.addEventListener('keydown', function (e) {
        if (e.key === 'ArrowDown') { e.preventDefault(); if (lista.classList.contains('hidden')) { abrir(); } else { activo = Math.min(activo + 1, mostrados.length - 1); pintar(); } }
        else if (e.key === 'ArrowUp') { e.preventDefault(); activo = Math.max(activo - 1, 0); pintar(); }
        else if (e.key === 'Enter') { if (!lista.classList.contains('hidden') && activo >= 0) { e.preventDefault(); elegir(mostrados[activo]); } }
        else if (e.key === 'Escape') { lista.classList.add('hidden'); }
    });
    entrada.addEventListener('blur', function () {
        lista.classList.add('hidden');
        if (oculto.value === '') { entrada.value = ''; }   // no se acepta texto que no sea de la lista
        validar();
    });

    validar();
})();
</script>
