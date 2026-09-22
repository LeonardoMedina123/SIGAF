<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGAF</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#0f2349] text-white antialiased">
    <div class="relative min-h-screen overflow-hidden bg-[#081c42]">
        <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ asset('fondo.jfif') }}')" aria-hidden="true"></div>
        <div class="absolute inset-0 bg-[#0d2d65]/60" aria-hidden="true"></div>

        <header class="relative z-10 flex w-full items-center justify-between px-4 md:px-10" aria-label="Encabezado institucional">
            <div class="flex items-center justify-start text-white drop-shadow-md">
                <img src="{{ asset('tecnm.png') }}" alt="Tecnológico Nacional de México" class="h-20 w-20 object-contain md:h-32 md:w-32" />
            </div>

            <div class="flex items-center justify-center text-white drop-shadow-md">
                <img src="{{ asset('logo_ITA.png') }}" alt="Instituto Tecnológico de Aguascalientes" class="h-20 w-20 object-contain md:h-28 md:w-28" />
            </div>

            <div class="flex items-center justify-end gap-2 text-white drop-shadow-md">
                <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-lg bg-[#7a1f5b]/80 px-1 py-1 shadow-[inset_0_0_0_1px_rgba(255,255,255,0.5)] md:h-14 md:w-14">
                    <div class="flex h-full w-full items-end justify-center gap-[3px]">
                        <span class="block h-1/4 w-[5px] rounded-t bg-white/90"></span>
                        <span class="block h-2/5 w-[5px] rounded-t bg-white/90"></span>
                        <span class="block h-3/5 w-[5px] rounded-t bg-white/90"></span>
                        <span class="block h-full w-[5px] rounded-t bg-white/90"></span>
                    </div>
                </div>
                <div class="flex flex-col leading-[0.9] text-center md:text-left">
                    <span class="text-[0.9rem] font-black tracking-[0.08em] text-white md:text-[1.5rem]">SIGAF</span>
                    <span class="text-[0.38rem] font-black uppercase tracking-[0.08em] text-white md:text-[0.52rem]">Sistema de gestión de<br />Archivos Financieros</span>
                </div>
            </div>
        </header>

        <main class="relative z-10 flex min-h-[calc(100vh-120px)] items-center justify-between gap-6 px-4 pb-8 md:px-8">
            <div class="ml-2 text-[clamp(4rem,7vw,8.5rem)] font-black leading-none tracking-[-0.08em] text-white/75 drop-shadow-[0_6px_15px_rgba(0,0,0,0.2)] md:ml-20">
                SIGAF
            </div>

            <div class="mb-4 flex w-full max-w-[530px] flex-col items-center md:mr-8 lg:mr-10">
                <section class="w-full overflow-hidden rounded-[18px] bg-white shadow-[0_18px_45px_rgba(0,0,0,0.2)] backdrop-blur-[2px]" aria-label="Inicio de sesión">
                    <div class="flex items-center justify-center gap-3 bg-[#004aad] px-4 py-4 text-white">
                        <img src="{{ asset('persona.png') }}" alt="Logo" class="h-8 w-8 object-contain" />
                        <h1 class="text-[2.1rem] font-black leading-none">Inicio de sesión</h1>
                    </div>

                    <form action="{{ route('login') }}" method="POST" class="space-y-5 p-5 md:p-6">
                        @csrf

                        <label class="block space-y-2">
                            <span class="block text-[1.05rem] font-bold text-[#173a6a]">Correo Laboral <em class="not-italic text-red-500">*</em></span>
                            <input name="email" type="email" value="23151256@aguascalientes.tecnm.mx" aria-label="Correo Laboral" class="w-full rounded-[10px] border-0 bg-slate-300/85 px-4 py-3 text-[1.05rem] text-[#1d3f73] shadow-inner shadow-slate-400/50 outline-none placeholder:text-slate-500 focus:ring-2 focus:ring-blue-500/50" />
                        </label>

                        <label class="block space-y-2">
                            <span class="block text-[1.05rem] font-bold text-[#173a6a]">Contraseña <em class="not-italic text-red-500">*</em></span>
                            <input name="password" type="password" aria-label="Contraseña" class="w-full rounded-[10px] border-0 bg-slate-300/85 px-4 py-3 text-[1.05rem] text-[#1d3f73] shadow-inner shadow-slate-400/50 outline-none placeholder:text-slate-500 focus:ring-2 focus:ring-blue-500/50" />
                        </label>

                        <div class="flex justify-end pt-1">
                            <button type="submit" class="w-[170px] rounded-xl bg-[#d1edfd] px-4 py-3 text-lg font-black text-[#ef3f43] transition hover:bg-sky-200">
                                INICIO
                            </button>
                        </div>
                    </form>
                </section>

                <div class="mt-4 flex items-center justify-center gap-2 text-center text-[1rem] font-bold text-white md:text-[1.1rem]">
                    <span>¿No tienes una cuenta?</span>
                    <a href="#" class="font-black underline decoration-white/80 underline-offset-4">CONSULTA</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>