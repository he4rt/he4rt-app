@use('App\Icons\Ios')
@use('App\Icons\Android')

<row class="items-center gap-2">
    <column class="w-7 h-7 items-center justify-center bg-theme-primary/15 rounded-[8]">
        <icon name="curlybraces" :ios="Ios::Curlybraces" :android="Android::Code" size="14" class="text-theme-primary" />
    </column>
    <text class="text-base text-theme-on-background font-bold">He4rt Devs</text>
</row>
