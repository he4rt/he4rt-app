<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Edge\NativeComponent;

class Timeline extends NativeComponent
{
    public string $activeFilter = 'todos';

    /**
     * Mock — a Timeline real vem de docs/plans/2026-09-22-api-mobile-jwt.md
     * no heartdevs.com (feature "Timeline"), ainda não existe API pra isso.
     *
     * @var list<array{author: string, role: ?string, timeAgo: string, body: string, code: ?array{file: string, lang: string, lines: list<string>}, tags: list<string>, likes: int, comments: int, category: string}>
     */
    public array $posts = [
        [
            'author' => 'time He4rt',
            'role' => 'CORE',
            'timeAgo' => 'há 2h',
            'body' => 'Refatoramos o pool de conexões do Postgres no gateway da API do heartdevs.com.',
            'code' => [
                'file' => 'config/database.php',
                'lang' => 'php',
                'lines' => [
                    "'pool' => ['min' => 5, 'max' => 50],",
                    '// timeout ajustado pra reduzir o P99',
                ],
            ],
            'tags' => ['#backend', '#postgres', '#performance'],
            'likes' => 84,
            'comments' => 23,
            'category' => 'projetos',
        ],
        [
            'author' => 'maria.dev',
            'role' => null,
            'timeAgo' => 'há 5h',
            'body' => 'A turma de mentoria de Setembro está com vagas abertas! Inscrições abertas até o dia 30.',
            'code' => null,
            'tags' => ['#mentoria', '#carreira'],
            'likes' => 41,
            'comments' => 12,
            'category' => 'mentorias',
        ],
        [
            'author' => 'time He4rt',
            'role' => 'CORE',
            'timeAgo' => 'ontem',
            'body' => 'Como fomos de 0 a 9 mil devs na comunidade — um resumo da jornada até aqui.',
            'code' => null,
            'tags' => [],
            'likes' => 156,
            'comments' => 37,
            'category' => 'geral',
        ],
        [
            'author' => 'pedro.silva',
            'role' => null,
            'timeAgo' => 'há 2 dias',
            'body' => 'Lancei meu primeiro projeto open source usando Laravel + Livewire. Feedback é bem-vindo.',
            'code' => null,
            'tags' => ['#laravel', '#livewire', '#opensource'],
            'likes' => 62,
            'comments' => 19,
            'category' => 'projetos',
        ],
        [
            'author' => 'time He4rt',
            'role' => 'CORE',
            'timeAgo' => 'há 3 dias',
            'body' => 'A He4rt Conf 2025 está confirmada pra Outubro, com inscrições abertas em breve.',
            'code' => null,
            'tags' => ['#he4rtconf'],
            'likes' => 203,
            'comments' => 58,
            'category' => 'eventos',
        ],
    ];

    public function setFilter(string $filter): void
    {
        $this->activeFilter = $filter;
    }

    /**
     * @return list<array{author: string, role: ?string, timeAgo: string, body: string, code: ?array{file: string, lang: string, lines: list<string>}, tags: list<string>, likes: int, comments: int, category: string}>
     */
    public function filteredPosts(): array
    {
        if ($this->activeFilter === 'todos') {
            return $this->posts;
        }

        return array_values(array_filter(
            $this->posts,
            fn (array $post): bool => $post['category'] === $this->activeFilter,
        ));
    }

    public function navTitle(): string
    {
        return 'Timeline';
    }

    public function render(): View
    {
        return view('native.timeline', [
            'posts' => $this->filteredPosts(),
        ]);
    }
}
