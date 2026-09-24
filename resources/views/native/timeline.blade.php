@use('App\Icons\Ios')
@use('App\Icons\Android')

@include('native.partials.coming-soon', [
    'icon' => 'bubble.left.and.bubble.right.fill',
    'iosIcon' => Ios::BubbleLeftAndBubbleRightFill,
    'androidIcon' => Android::Forum,
    'title' => 'Timeline chegando em breve',
    'description' => "A gente tá construindo a timeline da comunidade — publicações, projetos e novidades dos devs He4rt, direto no app.",
])
