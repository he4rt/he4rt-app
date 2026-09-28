@use('App\Icons\Ios')
@use('App\Icons\Android')

@include('native.partials.coming-soon', [
    'icon' => 'calendar',
    'iosIcon' => Ios::Calendar,
    'androidIcon' => Android::Event,
    'title' => 'Eventos chegando em breve',
    'description' => "Workshops, mentorias e encontros da comunidade — com inscrição e check-in direto pelo app.",
])
