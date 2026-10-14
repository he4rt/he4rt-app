@use('App\Icons\Ios')
@use('App\Icons\Android')

<column class="w-full h-full">
    <refreshable @refresh="refresh" class="w-full h-full bg-theme-background">
        <column class="w-full gap-5 p-4 pb-10">
            @if ($status === 'loading')
                <column class="w-full items-center gap-3 p-8">
                    <activity-indicator />
                    <text class="text-sm text-theme-on-surface-variant">Carregando evento...</text>
                </column>
            @elseif ($status === 'error')
                <column class="w-full items-center gap-3 p-8">
                    <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="28" class="text-theme-destructive" />
                    <text class="text-sm text-theme-destructive text-center">Não foi possível carregar o evento. Puxe pra baixo pra tentar de novo.</text>
                </column>
            @else
                {{-- Cabeçalho --}}
                <column class="w-full gap-2">
                    <row class="items-center gap-1.5 bg-theme-surface-variant rounded-full px-2.5 py-1 self-start">
                        <text class="text-xs text-theme-on-surface-variant font-semibold">{{ $typeLabel }}</text>
                    </row>
                    <text class="text-2xl text-theme-on-background font-extrabold">{{ $title }}</text>
                    <row class="items-center gap-1.5">
                        <icon name="calendar" :ios="Ios::Calendar" :android="Android::Event" size="14" class="text-theme-on-surface-variant" />
                        <text class="text-sm text-theme-on-surface-variant">{{ $when }}</text>
                    </row>
                    @if ($location)
                        <row class="items-center gap-1.5">
                            <icon name="mappin" :ios="Ios::Mappin" :android="Android::Place" size="14" class="text-theme-on-surface-variant" />
                            <text class="text-sm text-theme-on-surface-variant">{{ $location }}</text>
                        </row>
                    @endif
                </column>

                {{-- Status da inscrição --}}
                @if ($enrollmentStatusLabel)
                    <row class="items-center gap-2 bg-theme-{{ $enrollmentStatusToken }}/15 rounded-[14] p-3">
                        <icon name="checkmark.seal.fill" :ios="Ios::CheckmarkSeal" :android="Android::CheckCircle" size="16" class="text-theme-{{ $enrollmentStatusToken }}" />
                        <text class="text-sm text-theme-{{ $enrollmentStatusToken }}" font="medium">{{ $enrollmentStatusLabel }}</text>
                    </row>
                @endif

                {{-- Sobre --}}
                @if ($description)
                    <column class="w-full gap-2">
                        <text class="text-lg text-theme-on-background font-bold">Sobre</text>
                        <text class="text-sm text-theme-on-surface">{{ $description }}</text>
                    </column>
                @endif

                {{-- Ações --}}
                @if ($isEventFull)
                    <row class="items-center justify-center gap-2 bg-theme-destructive/15 rounded-[14] p-3">
                        <icon name="xmark.circle.fill" :ios="Ios::XmarkCircleFill" :android="Android::Cancel" size="16" class="text-theme-destructive" />
                        <text class="text-sm text-theme-destructive" font="medium">Evento lotado</text>
                    </row>
                @elseif ($canConfirmPresence)
                    <column class="w-full gap-2">
                        <button variant="primary" class="w-full" :loading="$isEnrolling" @press="enroll">Confirmar presença</button>
                        @if ($enrollError)
                            <text class="text-sm text-theme-destructive">{{ $enrollError }}</text>
                        @endif
                    </column>
                @elseif ($canApply)
                    <row class="items-center gap-2 bg-theme-surface-variant rounded-[14] p-3">
                        <icon name="clipboard" :ios="Ios::ClipboardFill" :android="Android::Assignment" size="16" class="text-theme-on-surface-variant" />
                        <text class="text-sm text-theme-on-surface-variant">Esse evento usa inscrição por formulário — ainda não disponível pelo app.</text>
                    </row>
                @endif

                @if ($canCheckIn)
                    <button variant="primary" class="w-full" @press="startCheckIn">Fazer check-in</button>
                @endif

                {{-- Área da staff — só aparece pra super admin (is_super_admin via GET /api/mobile/me) --}}
                @if ($isSuperAdmin)
                    <column class="w-full gap-3 bg-theme-secondary/10 border border-theme-secondary/30 rounded-[18] p-4 mt-2">
                        <row class="items-center gap-2">
                            <icon name="shield.fill" :ios="Ios::ShieldFill" :android="Android::AdminPanelSettings" size="16" class="text-theme-secondary" />
                            <text class="text-sm text-theme-secondary font-bold">Área da staff</text>
                        </row>

                        @if ($generatedCode)
                            <column class="w-full items-center gap-2 bg-theme-surface rounded-[14] p-4">
                                <text class="text-xs text-theme-on-surface-variant">Anuncie esse código pra galera digitar no check-in</text>
                                <text class="text-4xl text-theme-on-background font-extrabold" font="mono">{{ $generatedCode }}</text>
                                <text class="text-xs text-theme-on-surface-variant">Válido até {{ $generatedCodeExpiresAtLabel }}</text>
                                <pressable @press="dismissGeneratedCode">
                                    <text class="text-sm text-theme-primary" font="medium">Gerar outro</text>
                                </pressable>
                            </column>
                        @else
                            <row class="items-center gap-2">
                                <text class="text-xs text-theme-on-surface-variant">Dígitos:</text>
                                <chip label="4" :selected="$codeDigits === 4" @press="setCodeDigits(4)" />
                                <chip label="6" :selected="$codeDigits === 6" @press="setCodeDigits(6)" />
                            </row>

                            <button variant="secondary" class="w-full" :loading="$isGeneratingCode" @press="generateCheckInCode">Gerar código de check-in</button>

                            @if ($generateCodeError)
                                <text class="text-sm text-theme-destructive">{{ $generateCodeError }}</text>
                            @endif
                        @endif
                    </column>
                @endif
            @endif
        </column>
    </refreshable>

    {{--
        Check-in por código numérico (4 ou 6 dígitos). Único método
        self-service disponível — QR fica pra uma iteração futura.
    --}}
    <bottom-sheet :visible="$showCheckIn" @dismiss="cancelCheckIn">
        <column class="w-full gap-3 p-4">
            <text class="text-lg text-theme-on-surface font-bold">Check-in</text>

            <filled-text-input
                native:model="checkInCode"
                placeholder="Código de 4 ou 6 dígitos"
                keyboard="number"
                max-length="6"
            />

            @if ($checkInError)
                <text class="text-sm text-theme-destructive">{{ $checkInError }}</text>
            @endif

            <button variant="primary" :loading="$isCheckingIn" @press="submitCheckIn">Confirmar</button>
        </column>
    </bottom-sheet>
</column>
