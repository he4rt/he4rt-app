<column class="w-full h-full items-center justify-center gap-6 bg-theme-background safe-area">
    <stack class="w-[240] h-[208] items-center justify-center">
        <image :src="public_path('images/he4rt-hero-trace.png')" :width="240" :height="208" alt="" />

        <stack class="items-center justify-center">
            <circle
                :width="28" :height="28"
                class="bg-theme-primary opacity-25 rounded-full"
                :translate-x="$ledAX" :translate-y="$ledAY"
                :animate-duration="54" animate-easing="linear"
            />
            <circle
                :width="12" :height="12"
                class="bg-theme-primary rounded-full"
                :translate-x="$ledAX" :translate-y="$ledAY"
                :animate-duration="54" animate-easing="linear"
            />
        </stack>

        <stack class="items-center justify-center">
            <circle
                :width="28" :height="28"
                class="bg-theme-primary opacity-25 rounded-full"
                :translate-x="$ledBX" :translate-y="$ledBY"
                :animate-duration="54" animate-easing="linear"
            />
            <circle
                :width="12" :height="12"
                class="bg-theme-primary rounded-full"
                :translate-x="$ledBX" :translate-y="$ledBY"
                :animate-duration="54" animate-easing="linear"
            />
        </stack>
    </stack>

    <text class="text-lg text-theme-on-surface-variant" font="medium">He4rt Devs</text>
</column>
