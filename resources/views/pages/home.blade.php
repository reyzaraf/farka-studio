    <section id="home-page" class="page-section active">
        <div class="w-full h-[75vh] overflow-hidden relative">
            @php
                $videoUrl = $contact->video_url ?? 'https://www.pexels.com/download/video/36168059/';
                $isYoutube = false;
                $youtubeId = '';
                $isGdrive = false;
                $gdriveId = '';
                if (preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/\s]{11})%i', $videoUrl, $match)) {
                    $isYoutube = true;
                    $youtubeId = $match[1];
                } elseif (preg_match('%drive\.google\.com/(?:file/d/|open\?id=)([a-zA-Z0-9_-]+)%i', $videoUrl, $match)) {
                    $isGdrive = true;
                    $gdriveId = $match[1];
                }
            @endphp

            @if($isYoutube)
                <iframe id="hero-yt-video" class="absolute top-1/2 left-1/2 w-[300vw] h-[300vh] md:w-[150vw] md:h-[150vh] -translate-x-1/2 -translate-y-1/2 pointer-events-none opacity-0 transition-opacity duration-1000" 
                        data-src="https://www.youtube.com/embed/{{ $youtubeId }}?autoplay=1&mute=1&loop=1&playlist={{ $youtubeId }}&controls=0&showinfo=0&autohide=1&modestbranding=1" 
                        frameborder="0" allow="autoplay; fullscreen"></iframe>
            @elseif($isGdrive)
                <iframe id="hero-yt-video" class="absolute top-1/2 left-1/2 w-[300vw] h-[300vh] md:w-[150vw] md:h-[150vh] -translate-x-1/2 -translate-y-1/2 pointer-events-none opacity-0 transition-opacity duration-1000"
                        data-src="https://drive.google.com/file/d/{{ $gdriveId }}/preview"
                        frameborder="0" allow="autoplay; fullscreen"></iframe>
            @else
                <video id="hero-mp4-video" muted loop playsinline class="w-full h-full object-cover opacity-0 transition-opacity duration-1000">
                    <source src="{{ $videoUrl }}" type="video/mp4">
                </video>
            @endif
        </div>
    </section>
