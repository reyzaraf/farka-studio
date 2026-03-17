    <div id="bottom-bar" class="bottom-nav-bar flex flex-col justify-between px-6 md:px-[10%] py-6 md:py-8">
        <div class="flex justify-between items-start w-full">
            <div class="nav-btn relative inline-block cursor-pointer" onclick="goToProject()">
                <div class="btn-lines"><div class="btn-line-h btn-t"></div><div class="btn-line-v btn-r"></div></div>
                <div class="btn-text px-3 md:px-6 py-2 relative z-10 font-black uppercase text-sm md:text-xl tracking-widest">Project</div>
            </div>
            <div class="w-[60px] md:w-[150px]"></div>
            <div class="nav-btn relative inline-block cursor-pointer" onclick="goToAbout()">
                <div class="btn-lines"><div class="btn-line-h btn-t"></div><div class="btn-line-v btn-r"></div></div>
                <div class="btn-text px-3 md:px-6 py-2 relative z-10 font-black uppercase text-sm md:text-xl tracking-widest">About</div>
            </div>
        </div>
        
        <div class="flex justify-between items-end w-full text-[0.55rem] md:text-[0.65rem] font-bold uppercase tracking-widest text-gray-400">
            <div class="leading-relaxed w-[45%] md:w-auto">
                {!! nl2br(e($contact->address ?? "Jl. Kemang Raya No. 88\nJakarta Selatan 12730")) !!}
                <div class="mt-4 text-[0.45rem] md:text-[0.55rem] text-gray-300 normal-case tracking-normal">Copyright &copy; {{ date('Y') }} Farka Studio. All rights reserved.</div>
            </div>
            <div class="flex flex-col md:flex-row gap-2 md:gap-10 items-end text-right w-[50%] md:w-auto">
                <div class="leading-relaxed order-last md:order-first whitespace-nowrap">{{ $contact->email ?? 'admin@farkastudio.id' }}<br>{{ $contact->phone ?? '+62 812 3456 78' }}</div>
                <div class="nav-btn relative inline-block cursor-pointer order-first md:order-last mb-1 md:mb-0" onclick="goToContact()">
                    <div class="btn-lines"><div class="btn-line-h btn-t"></div><div class="btn-line-v btn-r"></div></div>
                    <div class="btn-text px-3 md:px-4 py-1.5 md:py-1 relative z-10 text-black font-black text-[0.6rem] md:text-sm whitespace-nowrap">Contact Us</div>
                </div>
            </div>
        </div>
    </div>
