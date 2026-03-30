    <section id="contact-page" class="page-section bg-white pt-24 md:pt-40 px-6 md:px-20">
        <div class="fixed top-8 md:top-20 right-6 md:right-20 -translate-y-1/2 font-black cursor-pointer border-b-2 border-black text-[0.65rem] md:text-xs tracking-tight transition hover:text-gray-500 z-50" onclick="goToHome()">Back to Home</div>
        
        <div class="flex flex-col items-center justify-center h-[calc(100vh-25vh)] md:h-full pb-20 md:pb-32 overflow-y-auto scroll-container">
            <h2 class="text-3xl md:text-5xl font-black mb-10 tracking-tight text-center">Get In Touch</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-10 md:gap-20 text-sm font-bold tracking-tight text-black w-full max-w-4xl">
                <div class="text-center flex flex-col items-center">
                    <h4 class="text-gray-400 mb-3 whitespace-nowrap text-base">Email Inquiries</h4>
                    <a href="mailto:{{ $contact->email ?? 'admin@farkastudio.id' }}" class="hover:text-gray-500 transition whitespace-nowrap">{{ $contact->email ?? 'admin@farkastudio.id' }}</a>
                </div>
                <div class="text-center flex flex-col items-center border-y md:border-y-0 md:border-l md:border-r border-gray-200 py-8 md:py-0 px-0 md:px-8">
                    <h4 class="text-gray-400 mb-3 whitespace-nowrap text-base">Give Us a Call</h4>
                    @php
                        $phoneContact = $contact->phone ?? '+62 812 3456 78';
                        $waContact = preg_replace('/[^0-9]/', '', $phoneContact);
                        if (str_starts_with($waContact, '0')) $waContact = '62' . substr($waContact, 1);
                    @endphp
                    <a href="https://wa.me/{{ $waContact }}" target="_blank" class="hover:text-gray-500 transition whitespace-nowrap">{{ $phoneContact }}</a>
                </div>
                <div class="text-center flex flex-col items-center">
                    <h4 class="text-gray-400 mb-3 whitespace-nowrap text-base">Farka HQ</h4>
                    <div class="leading-tight">{!! nl2br(e($contact->address ?? "Jl. Kemang Raya No. 88\nJakarta Selatan 12730")) !!}</div>
                </div>
            </div>
        </div>
    </section>
