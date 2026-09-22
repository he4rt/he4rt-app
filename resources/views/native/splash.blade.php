<column class="w-full h-full items-center justify-center gap-6 bg-theme-background safe-area">
    <stack class="h-56 w-56 items-center justify-center">
        <canvas class="h-56 w-56">
            <stack class="flex-1 items-center justify-center">
                <circle
                    :width="176" :height="176"
                    class="bg-theme-primary opacity-20 rounded-full"
                    :scale="1.3" animate-loop :animate-duration="1800" animate-easing="ease-in-out"
                />
                <circle
                    :width="140" :height="140"
                    class="bg-theme-primary opacity-10 rounded-full"
                    :scale="1.45" animate-loop :animate-duration="1800" animate-easing="ease-in-out"
                />
            </stack>
        </canvas>

        <image
            :src="public_path('images/he4rt-mark.png')"
            :width="88" :height="75"
            :tint-color="theme('primary')"
            alt="He4rt"
            :scale="1.06" animate-loop :animate-duration="900" animate-easing="ease-in-out"
        />
    </stack>

    <text class="text-lg text-theme-on-surface-variant" font="medium">He4rt Devs</text>
</column>
