<header class="relative flex h-[58px] items-center justify-between overflow-hidden border-b-2 border-[#8050e8] bg-[#754b2e] px-3 text-white shadow-sm md:px-6">
	<div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('fondo.jfif') }}')" aria-hidden="true"></div>
	<div class="absolute inset-0 bg-[#4b2716]/45" aria-hidden="true"></div>

	<div class="relative z-10 flex min-w-0 items-center gap-3">
		<div class="flex h-10 w-10 shrink-0 items-center justify-center overflow-hidden rounded-full p-1 shadow">
			<img src="{{ asset('persona.png') }}" alt="" class="h-full w-full object-contain" />
		</div>
		<div class="min-w-0 text-[13px] font-semibold leading-[1.05] drop-shadow-sm">
			<p class="max-w-[220px] truncate">{{ auth()->user()->name }}</p>
			<p class="max-w-[220px] truncate">{{ auth()->user()->isSupplier() ? 'Proveedor' : auth()->user()->department?->name }}</p>
		</div>
	</div>

	<img src="{{ asset('tecnm.png') }}" alt="Tecnológico Nacional de México" class="relative z-10 h-9 w-auto max-w-[130px] object-contain drop-shadow-sm md:max-w-[180px]" />
</header>
