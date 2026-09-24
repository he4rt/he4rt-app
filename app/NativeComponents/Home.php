<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;
use Native\Mobile\Facades\Browser;

class Home extends NativeComponent
{
    /**
     * Mock — a API real de estatísticas da comunidade ainda não existe
     * (ver docs/plans/2026-09-22-api-mobile-jwt.md no heartdevs.com).
     *
     * @var array{membros: string, online: string, mensagens: string}
     */
    public array $stats = [
        'membros' => '9.2k+',
        'online' => '184',
        'mensagens' => '1.3k',
    ];

    /**
     * Mock — Eventos vem de docs/plans/2026-09-22-api-mobile-jwt.md
     * (feature "Eventos", ainda com gap de domínio pro check-in por QR).
     * `statusToken` é um token de cor do tema (`primary` | `success`).
     *
     * @var list<array{title: string, when: string, status: string, statusToken: string}>
     */
    public array $upcomingEvents = [
        [
            'title' => 'Workshop: Laravel na prática',
            'when' => 'Qui, 25 Set · 19h',
            'status' => 'Em breve',
            'statusToken' => 'primary',
        ],
        [
            'title' => 'Mentoria coletiva de carreira',
            'when' => 'Sáb, 27 Set · 10h',
            'status' => 'Vagas abertas',
            'statusToken' => 'success',
        ],
    ];

    /**
     * Mock — post fixado da Timeline, com um trecho de código pra dar cara
     * de comunidade dev. Vem de docs/plans/2026-09-22-api-mobile-jwt.md
     * (feature "Timeline").
     *
     * @var array{author: string, timeAgo: string, body: string, codeFile: string, codeLang: string, codeLines: list<string>, tags: list<string>, likes: int, comments: int}
     */
    public array $featuredPost = [
        'author' => 'time He4rt',
        'timeAgo' => 'há 2h',
        'body' => 'Refatoramos o pool de conexões do Postgres no gateway da API do heartdevs.com.',
        'codeFile' => 'config/database.php',
        'codeLang' => 'php',
        'codeLines' => [
            "'pool' => ['min' => 5, 'max' => 50],",
            '// timeout ajustado pra reduzir o P99',
        ],
        'tags' => ['#backend', '#postgres', '#performance'],
        'likes' => 84,
        'comments' => 23,
    ];

    /**
     * Mock — restante da Timeline, em formato compacto.
     *
     * @var list<array{title: string, author: string, timeAgo: string}>
     */
    public array $latestPosts = [
        [
            'title' => 'Turma de mentoria de Setembro está com vagas abertas',
            'author' => 'maria.dev',
            'timeAgo' => 'há 5h',
        ],
        [
            'title' => 'Como fomos de 0 a 9 mil devs na comunidade',
            'author' => 'time He4rt',
            'timeAgo' => 'ontem',
        ],
    ];

    public function goToEventos(): void
    {
        $this->replace('/eventos');
    }

    public function goToTimeline(): void
    {
        $this->replace('/timeline');
    }

    public function goToDiscord(): void
    {
        Browser::open('https://discord.com/invite/he4rt');
    }

    public function navTitle(): string
    {
        return 'Início';
    }

    public function render(): View
    {
        return view('native.home');
    }
}
