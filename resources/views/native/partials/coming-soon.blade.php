{{--
    Estado "em construção" compartilhado pelas abas cuja API real (ver
    docs/plans/2026-09-22-api-mobile-jwt.md no heartdevs.com) ainda não
    existe. Espera $icon/$iosIcon/$androidIcon, $title, $description.
--}}
<column class="w-full h-full items-center justify-center gap-4 p-8">
    <column class="w-20 h-20 items-center justify-center bg-theme-primary/15 rounded-full">
        <icon name="{{ $icon }}" :ios="$iosIcon" :android="$androidIcon" size="36" class="text-theme-primary" />
    </column>

    <text class="text-xl text-theme-on-surface font-bold text-center">{{ $title }}</text>
    <text class="text-sm text-theme-on-surface-variant text-center">{{ $description }}</text>

    <badge label="Em desenvolvimento" variant="primary" class="mt-2" />
</column>
