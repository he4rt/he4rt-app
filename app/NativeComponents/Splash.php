<?php

declare(strict_types=1);

namespace App\NativeComponents;

use Illuminate\View\View;
use Native\Mobile\Attributes\Poll;
use Native\Mobile\Edge\NativeComponent;

/**
 * Reproduz app-modules/portal/resources/views/components/animated-logo.blade.php
 * (heartdevs.com) nativamente: o EDGE não desenha paths/stroke-dasharray
 * arbitrários, então os pontos abaixo são as mesmas duas curvas SVG do
 * componente web amostradas em 48 pontos por segmento (arc-length uniforme,
 * via svgpathtools) e centralizadas num container de 240x208 — dois "LEDs"
 * seguem cada trilha via translate-x/y, um frame por tick de poll, no mesmo
 * ritmo da animação CSS original (2.6s por volta, o segundo LED meia volta
 * atrás do primeiro).
 */
class Splash extends NativeComponent
{
    private const int LAP_MS = 2_600;

    private const int FRAME_COUNT = 48;

    // 2_600 / 48 arredondado pra baixo — intdiv() não é uma expressão
    // constante válida pra inicializar uma const tipada.
    private const int FRAME_MS = 54;

    /** @var list<array{0: float, 1: float}> */
    private const array LED_A = [
        [11.34, -80.49], [0.74, -69.89], [-9.87, -59.29], [-20.47, -48.68], [-31.07, -38.08],
        [-41.68, -27.48], [-52.28, -16.88], [-62.89, -18.47], [-73.12, -29.39], [-75.49, -43.81],
        [-66.91, -55.63], [-52.47, -57.85], [-40.3, -55.14], [-29.69, -65.74], [-19.09, -76.34],
        [-22.47, -86.33], [-35.68, -93.35], [-50.23, -96.78], [-65.18, -96.41], [-79.55, -92.25],
        [-92.39, -84.59], [-102.86, -73.91], [-110.28, -60.93], [-114.16, -46.49], [-114.25, -31.53],
        [-110.53, -17.05], [-103.26, -3.98], [-93.14, 7.06], [-82.53, 17.66], [-71.93, 28.27],
        [-61.33, 38.87], [-50.72, 37.35], [-40.12, 26.74], [-29.52, 16.14], [-18.91, 5.54],
        [-8.31, -5.07], [2.29, -15.67], [12.9, -26.27], [23.5, -36.88], [34.1, -47.48],
        [44.71, -58.09], [55.31, -68.69], [65.91, -79.29], [76.52, -89.9], [66.61, -95.92],
        [51.75, -97.59], [36.95, -95.47], [23.17, -89.64],
    ];

    /** @var list<array{0: float, 1: float}> */
    private const array LED_B = [
        [105.35, -64.37], [98.46, -58.19], [91.42, -51.14], [84.37, -44.1], [77.33, -37.05],
        [70.29, -30.01], [63.24, -22.96], [56.2, -15.92], [49.16, -15.85], [42.12, -22.9],
        [35.07, -22.16], [28.03, -15.11], [20.99, -8.07], [13.94, -1.03], [15.25, 6.02],
        [22.3, 13.06], [20.16, 20.11], [13.12, 27.15], [6.07, 34.2], [-0.97, 41.24],
        [-8.01, 48.28], [-15.06, 55.33], [-22.1, 62.37], [-29.15, 69.42], [-23.73, 76.46],
        [-16.68, 83.51], [-9.64, 90.55], [-2.6, 97.6], [4.45, 91.62], [11.49, 84.57],
        [18.54, 77.53], [25.58, 70.49], [32.62, 63.44], [39.67, 56.4], [46.71, 49.35],
        [53.76, 42.31], [60.8, 35.26], [67.85, 28.22], [74.89, 21.18], [81.93, 14.13],
        [88.98, 7.09], [95.46, 0.55], [101.58, -7.29], [106.28, -16.06], [109.43, -25.5],
        [110.93, -35.34], [110.74, -45.29], [108.86, -55.06],
    ];

    public int $tick = 0;

    public float $ledAX = self::LED_A[0][0];

    public float $ledAY = self::LED_A[0][1];

    public float $ledBX = 0.0;

    public float $ledBY = 0.0;

    public function mount(): void
    {
        // LED "b" começa meia volta (24 de 48 frames) atrás do "a", igual ao
        // animation-delay: 1.3s (metade do ciclo de 2.6s) do CSS original.
        $half = intdiv(self::FRAME_COUNT, 2);
        $this->ledBX = self::LED_B[$half][0];
        $this->ledBY = self::LED_B[$half][1];
    }

    #[Poll(self::FRAME_MS)]
    public function advance(): void
    {
        $this->tick = ($this->tick + 1) % self::FRAME_COUNT;

        $a = self::LED_A[$this->tick];
        $this->ledAX = $a[0];
        $this->ledAY = $a[1];

        $b = self::LED_B[($this->tick + intdiv(self::FRAME_COUNT, 2)) % self::FRAME_COUNT];
        $this->ledBX = $b[0];
        $this->ledBY = $b[1];
    }

    #[Poll(self::LAP_MS)]
    public function finish(): void
    {
        $this->replace('/home');
    }

    public function navTitle(): string
    {
        return '';
    }

    public function render(): View
    {
        return view('native.splash');
    }
}
