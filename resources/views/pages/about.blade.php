    <section id="about-page" class="page-section bg-white pt-24 md:pt-40 px-6 md:px-20">
        <div class="fixed top-8 md:top-20 right-6 md:right-20 -translate-y-1/2 font-black cursor-pointer uppercase border-b-2 border-black text-[0.65rem] md:text-xs tracking-widest transition hover:text-gray-500 z-50" onclick="goToHome()">Back to Home</div>
        
        <div class="h-[calc(100vh-25vh)] md:h-full w-full overflow-y-auto scroll-container pb-32">
            <div class="max-w-4xl mx-auto mb-16 md:mb-24 pt-4 md:pt-10 text-center">
                <h2 class="text-3xl md:text-4xl font-black uppercase mb-6 md:mb-8 tracking-widest border-b-2 border-black pb-4 inline-block">The Studio</h2>
                <p class="text-gray-500 leading-relaxed text-sm md:text-lg">
                    Farka Studio adalah biro arsitektur dan desain interior yang berpusat di Indonesia. Kami percaya bahwa arsitektur bukan sekadar tentang membangun struktur, melainkan menciptakan ruang yang mampu bernapas, berdialog dengan lingkungannya, dan memberikan dampak positif bagi kehidupan manusia di dalamnya. 
                    <br><br>
                    Pendekatan kami selalu berakar pada kesederhanaan bentuk, kejujuran material, dan keberlanjutan. Melalui proses kolaboratif yang erat antara arsitek, klien, dan pembuat karya, Farka Studio terus mengeksplorasi batas-batas ruang untuk menghadirkan desain yang fungsional sekaligus berjiwa.
                </p>
            </div>

            <div class="w-full h-[40vh] md:h-[60vh] mb-20 md:mb-32 relative">
                <img src="https://images.pexels.com/photos/3184360/pexels-photo-3184360.jpeg" alt="Farka Team" class="w-full h-full object-cover grayscale hover:grayscale-0 transition duration-700 ease-in-out">
                <div class="absolute bottom-4 right-6 text-white font-bold tracking-widest text-[0.6rem] md:text-xs uppercase opacity-70 bg-black/30 px-2 py-1 rounded">Farka Studio Collective, 2026</div>
            </div>

            <div class="max-w-6xl mx-auto">
                <h3 class="text-xl md:text-2xl font-black uppercase mb-8 md:mb-12 tracking-widest text-center md:text-left">Key People</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-10">
                    @foreach($keyPeople as $person)
                    <div class="group cursor-pointer text-center md:text-left">
                        <div class="overflow-hidden mb-4">
                            @if($person->image_url)
                                <img src="{{ asset('storage/' . $person->image_url) }}" alt="{{ $person->name }}" class="w-full aspect-[3/4] object-cover grayscale group-hover:grayscale-0 group-hover:scale-105 transition duration-500">
                            @else
                                <div class="w-full aspect-[3/4] bg-gray-200 flex items-center justify-center text-gray-400 grayscale group-hover:grayscale-0 transition duration-500">No Image</div>
                            @endif
                        </div>
                        <h4 class="font-black uppercase tracking-wider text-sm md:text-lg">{{ $person->name }}</h4>
                        <p class="text-[0.65rem] md:text-xs text-gray-400 uppercase tracking-widest mt-1 font-bold">{{ $person->role }}</p>
                        @if($person->description)
                            <p class="text-xs text-gray-500 mt-2">{{ $person->description }}</p>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
