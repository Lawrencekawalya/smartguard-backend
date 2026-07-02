<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { dashboard, login } from '@/routes';
import { register } from '@/routes';
</script>

<template>
    <Head title="Welcome">
        <link rel="preconnect" href="https://rsms.me/" />
        <link rel="stylesheet" href="https://rsms.me/inter/inter.css" />
    </Head>
    <div
        class="flex min-h-screen flex-col items-center bg-[#FDFDFC] p-6 text-[#1b1b18] lg:justify-center lg:p-8 dark:bg-[#0a0a0a]"
    >
        <header
            class="mb-6 w-full max-w-[335px] text-sm not-has-[nav]:hidden lg:max-w-4xl"
        >
            <nav class="flex items-center justify-end gap-4">
                <Link
                    v-if="$page.props.auth.user"
                    :href="dashboard()"
                    class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                >
                    Dashboard
                </Link>
                <template v-else>
                    <Link
                        :href="login()"
                        class="inline-block rounded-sm border border-transparent px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#19140035] dark:text-[#EDEDEC] dark:hover:border-[#3E3E3A]"
                    >
                        Log in
                    </Link>
                    <Link
                        :href="register()"
                        class="inline-block rounded-sm border border-[#19140035] px-5 py-1.5 text-sm leading-normal text-[#1b1b18] hover:border-[#1915014a] dark:border-[#3E3E3A] dark:text-[#EDEDEC] dark:hover:border-[#62605b]"
                    >
                        Register
                    </Link>
                </template>
            </nav>
        </header>
        <div
            class="flex w-full items-center justify-center opacity-100 transition-opacity duration-750 lg:grow starting:opacity-0"
        >
            <main
                class="flex w-full max-w-[335px] flex-col-reverse overflow-hidden rounded-lg lg:max-w-4xl lg:flex-row"
            >
                <div
                    class="flex-1 rounded-br-lg rounded-bl-lg bg-white p-6 pb-12 text-[13px] leading-[20px] shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-tl-lg lg:rounded-br-none lg:p-20 dark:bg-[#161615] dark:text-[#EDEDEC] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <h1 class="mb-1 font-medium">
                        Protect and monitor your electrical system
                    </h1>
                    <p class="mb-2 text-[#706f6c] dark:text-[#A1A09A]">
                        SmartGuard tracks voltage, current, power, energy use,
                        relay activity, and fault events from your connected
                        devices.
                    </p>
                    <ul class="mb-4 flex flex-col lg:mb-6">
                        <li
                            class="relative flex items-center gap-4 py-2 before:absolute before:top-1/2 before:bottom-0 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]"
                        >
                            <span
                                class="relative bg-white py-1 dark:bg-[#161615]"
                            >
                                <span
                                    class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]"
                                    />
                                </span>
                            </span>
                            <span>
                                Review system setup in the
                                <a
                                    href="https://smartguard.syntaxsystems.co/documentation"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="ml-1 inline-flex items-center space-x-1 font-medium text-[#00A344] underline underline-offset-4 dark:text-[#A7FFBC]"
                                >
                                    <span>Documentation</span>
                                    <svg
                                        width="10"
                                        height="11"
                                        viewBox="0 0 10 11"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-2.5 w-2.5"
                                    >
                                        <path
                                            d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                            stroke="currentColor"
                                            stroke-linecap="square"
                                        />
                                    </svg>
                                </a>
                            </span>
                        </li>
                        <li
                            class="relative flex items-center gap-4 py-2 before:absolute before:top-0 before:bottom-1/2 before:left-[0.4rem] before:border-l before:border-[#e3e3e0] dark:before:border-[#3E3E3A]"
                        >
                            <span
                                class="relative bg-white py-1 dark:bg-[#161615]"
                            >
                                <span
                                    class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-[#e3e3e0] bg-[#FDFDFC] shadow-[0px_0px_1px_0px_rgba(0,0,0,0.03),0px_1px_2px_0px_rgba(0,0,0,0.06)] dark:border-[#3E3E3A] dark:bg-[#161615]"
                                >
                                    <span
                                        class="h-1.5 w-1.5 rounded-full bg-[#dbdbd7] dark:bg-[#3E3E3A]"
                                    />
                                </span>
                            </span>
                            <span>
                                View live protection data in the
                                <Link
                                    :href="dashboard()"
                                    class="ml-1 inline-flex items-center space-x-1 font-medium text-[#00A344] underline underline-offset-4 dark:text-[#A7FFBC]"
                                >
                                    <span>Dashboard</span>
                                    <svg
                                        width="10"
                                        height="11"
                                        viewBox="0 0 10 11"
                                        fill="none"
                                        xmlns="http://www.w3.org/2000/svg"
                                        class="h-2.5 w-2.5"
                                    >
                                        <path
                                            d="M7.70833 6.95834V2.79167H3.54167M2.5 8L7.5 3.00001"
                                            stroke="currentColor"
                                            stroke-linecap="square"
                                        />
                                    </svg>
                                </Link>
                            </span>
                        </li>
                    </ul>
                    <ul class="flex gap-3 text-sm leading-normal">
                        <li>
                            <Link
                                :href="
                                    $page.props.auth.user
                                        ? dashboard()
                                        : login()
                                "
                                class="inline-block rounded-sm border border-[#102A18] bg-[#102A18] px-5 py-1.5 text-sm leading-normal text-white hover:border-[#07120B] hover:bg-[#07120B] dark:border-[#A7FFBC] dark:bg-[#A7FFBC] dark:text-[#07120B] dark:hover:border-white dark:hover:bg-white"
                            >
                                Open dashboard
                            </Link>
                        </li>
                    </ul>
                </div>
                <div
                    class="relative -mb-px aspect-[335/364] w-full shrink-0 overflow-hidden rounded-t-lg bg-[#EFFFF3] lg:mb-0 lg:-ml-px lg:aspect-auto lg:w-[438px] lg:rounded-t-none lg:rounded-r-lg dark:bg-[#06170B]"
                >
                    <!-- SmartGuard Logo -->
                    <svg
                        class="w-full max-w-none translate-y-0 text-[#00C853] opacity-100 transition-all duration-750 dark:text-[#A7FFBC] starting:opacity-0 motion-safe:starting:translate-y-6"
                        viewBox="0 0 438 104"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        role="img"
                        aria-label="SmartGuard"
                    >
                        <path
                            fill="#102A18"
                            d="M20 14h68a10 10 0 0 1 10 10v60a10 10 0 0 1-10 10H20a10 10 0 0 1-10-10V24a10 10 0 0 1 10-10Z"
                        />
                        <path
                            d="m54 21 26 10v19c0 18-10.9 31.8-26 38-15.1-6.2-26-20-26-38V31l26-10Z"
                            fill="currentColor"
                        />
                        <path
                            fill="#07120B"
                            d="m54 29 18 7v14c0 12.4-7.1 22.5-18 27.7C43.1 72.5 36 62.4 36 50V36l18-7Z"
                        />
                        <path
                            d="M54 38a11 11 0 1 0 .1 0H54Zm0 6a5 5 0 1 1-.1 0h.1Z"
                            fill="currentColor"
                            fill-rule="evenodd"
                            clip-rule="evenodd"
                        />
                        <path fill="currentColor" d="M51 56h6v14h-6z" />
                        <path fill="#A7FFBC" d="M45 81h18v5H45z" />
                        <path
                            fill="#A7FFBC"
                            d="m62 42-7 11h8l-15 21 4-15h-7l6-17h11Z"
                            opacity=".95"
                        />
                        <text
                            x="118"
                            y="57"
                            fill="currentColor"
                            font-family="Inter, ui-sans-serif, system-ui, sans-serif"
                            font-size="42"
                            font-weight="700"
                            letter-spacing="0"
                        >
                            SmartGuard
                        </text>
                        <text
                            x="120"
                            y="80"
                            fill="currentColor"
                            font-family="Inter, ui-sans-serif, system-ui, sans-serif"
                            font-size="15"
                            font-weight="500"
                            letter-spacing="0"
                            opacity=".78"
                        >
                            Intelligent electrical protection
                        </text>
                    </svg>

                    <!-- Voltage waveform -->
                    <svg
                        class="relative -mt-[6.6rem] -ml-8 w-[438px] max-w-none [--stroke-color:#1B1B18] lg:ml-0 dark:[--stroke-color:#00C853]"
                        viewBox="0 0 440 392"
                        fill="none"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >
                        <defs>
                            <path
                                id="voltage-waveform"
                                d="M-6 245H35C48 245 54 221 66 221C81 221 84 294 101 294C119 294 123 106 144 106C165 106 169 294 188 294C207 294 211 159 229 159C248 159 251 245 269 245H310C323 245 329 221 341 221C356 221 359 294 376 294C394 294 398 106 419 106C440 106 444 294 463 294"
                            />
                            <path
                                id="voltage-pulse"
                                d="M-4 246H83L106 246L123 198L146 304L171 139L196 246H464"
                            />
                        </defs>
                        <g
                            class="text-[#1B1B18] opacity-100 mix-blend-darken transition-all delay-300 duration-750 dark:text-black dark:mix-blend-normal starting:opacity-0"
                        >
                            <path
                                d="M0 246H440"
                                stroke="currentColor"
                                stroke-width="5"
                                stroke-linecap="round"
                                opacity=".22"
                            />
                            <use
                                href="#voltage-waveform"
                                stroke="currentColor"
                                stroke-width="34"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </g>

                        <g
                            class="text-[#B8F7C6] opacity-100 transition-all delay-400 duration-750 dark:text-[#0D3A18] starting:opacity-0 motion-safe:starting:-translate-x-[26px]"
                        >
                            <use
                                href="#voltage-waveform"
                                stroke="currentColor"
                                stroke-width="31"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </g>

                        <g
                            class="text-[#00C853] opacity-100 mix-blend-multiply transition-all delay-400 duration-750 dark:text-[#164F23] dark:mix-blend-screen starting:opacity-0 motion-safe:starting:-translate-x-[51px]"
                        >
                            <use
                                href="#voltage-waveform"
                                stroke="currentColor"
                                stroke-width="25"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </g>

                        <g
                            class="text-[#A7FFBC] opacity-100 mix-blend-multiply transition-all delay-400 duration-750 dark:text-[#1E6C31] dark:mix-blend-normal starting:opacity-0 motion-safe:starting:-translate-x-[78px]"
                        >
                            <use
                                href="#voltage-waveform"
                                stroke="currentColor"
                                stroke-width="18"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                        </g>

                        <g
                            class="text-[#102A18] opacity-100 mix-blend-overlay transition-all delay-400 duration-750 dark:text-[#A7FFBC] starting:opacity-0 motion-safe:starting:-translate-x-[102px]"
                        >
                            <path
                                d="M47 246H395"
                                stroke="currentColor"
                                stroke-width="4"
                                stroke-linecap="round"
                                opacity=".35"
                            />
                            <use
                                href="#voltage-pulse"
                                stroke="var(--stroke-color)"
                                stroke-width="6"
                                stroke-linecap="round"
                                stroke-linejoin="round"
                            />
                            <path
                                d="M366 179L344 218H371L334 289L344 239H319L340 179H366Z"
                                fill="currentColor"
                                opacity=".7"
                            />
                        </g>
                    </svg>
                    <div
                        class="absolute inset-0 rounded-t-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] lg:rounded-t-none lg:rounded-r-lg dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    ></div>
                </div>
            </main>
        </div>
        <div class="hidden h-14.5 lg:block"></div>
    </div>
</template>
