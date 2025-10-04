<script setup lang="ts">
import { login, register } from '@/routes';
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Form } from '@inertiajs/vue3';
import RegisteredUserController from '@/actions/App/Http/Controllers/Auth/RegisteredUserController';
import AuthenticatedSessionController from '@/actions/App/Http/Controllers/Auth/AuthenticatedSessionController';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { LoaderCircle, X } from 'lucide-vue-next';

const showLoginForm = ref(false);
const showRegisterForm = ref(false);

const openLoginForm = () => {
    showRegisterForm.value = false;
    showLoginForm.value = true;
};

const openRegisterForm = () => {
    showLoginForm.value = false;
    showRegisterForm.value = true;
};

const closeForms = () => {
    showLoginForm.value = false;
    showRegisterForm.value = false;
};
</script>

<template>
    <Head title="AgroSpace - Farm Game">
        <link rel="preconnect" href="https://fonts.googleapis.com/" />
        <link rel="preconnect" href="https://fonts.gstatic.com/" crossorigin />
        <link
            href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap"
            rel="stylesheet"
        />
    </Head>

    <div
        class="relative min-h-screen overflow-hidden bg-gradient-to-b from-sky-300 via-sky-200 to-green-200"
    >
        <!-- Nubes animadas -->
        <div class="pointer-events-none absolute inset-0">
            <div
                class="cloud cloud-1 animate-float absolute top-10 left-10 h-12 w-20 rounded-full bg-white opacity-80"
            ></div>
            <div
                class="cloud cloud-2 animate-float-delayed absolute top-20 right-20 h-10 w-16 rounded-full bg-white opacity-70"
            ></div>
            <div
                class="cloud cloud-3 animate-float-slow absolute top-32 left-1/3 h-14 w-24 rounded-full bg-white opacity-60"
            ></div>
        </div>

        <!-- Sol animado -->
        <div class="animate-spin-slow absolute top-8 right-8 h-16 w-16">
            <svg viewBox="0 0 64 64" class="h-full w-full">
                <circle cx="32" cy="32" r="20" fill="#FFD700" />
                <g stroke="#FFD700" stroke-width="3" stroke-linecap="round">
                    <line x1="32" y1="4" x2="32" y2="12" />
                    <line x1="32" y1="52" x2="32" y2="60" />
                    <line x1="4" y1="32" x2="12" y2="32" />
                    <line x1="52" y1="32" x2="60" y2="32" />
                    <line x1="11.76" y1="11.76" x2="17.17" y2="17.17" />
                    <line x1="46.83" y1="46.83" x2="52.24" y2="52.24" />
                    <line x1="11.76" y1="52.24" x2="17.17" y2="46.83" />
                    <line x1="46.83" y1="17.17" x2="52.24" y2="11.76" />
                </g>
            </svg>
        </div>

        <!-- Contenido principal -->
        <main
            class="relative z-10 flex min-h-[calc(100vh-120px)] flex-col items-center justify-center px-6"
        >
            <div class="animate-fade-in mb-8 text-center">
                <h1
                    class="font-fredoka mb-4 text-6xl font-bold text-green-800 drop-shadow-lg md:text-8xl"
                >
                    AgroSpace
                </h1>
                <p
                    class="font-fredoka text-xl font-medium text-green-700 md:text-2xl"
                >
                    ¡Construye tu granja de ensueño!
                </p>
                <p class="font-fredoka mt-2 text-lg text-green-600">
                    Siembra, riega, cosecha y expande tu imperio agrícola
                </p>
            </div>

            <!-- Botones de acción principales -->
            <div class="animate-slide-up flex flex-col gap-6 sm:flex-row justify-center items-center mb-8">
                <button
                    @click="openLoginForm"
                    class="group font-fredoka flex transform items-center gap-3 rounded-2xl bg-gradient-to-r from-green-500 to-green-600 px-8 py-4 text-lg font-bold text-white shadow-xl transition-all duration-300 hover:scale-105 hover:from-green-600 hover:to-green-700"
                >
                    <svg
                        class="h-6 w-6 transition-transform group-hover:rotate-12"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            fill-rule="evenodd"
                            d="M3 3a1 1 0 011 1v12a1 1 0 11-2 0V4a1 1 0 011-1zm7.707 3.293a1 1 0 010 1.414L9.414 9H17a1 1 0 110 2H9.414l1.293 1.293a1 1 0 01-1.414 1.414l-3-3a1 1 0 010-1.414l3-3a1 1 0 011.414 0z"
                            clip-rule="evenodd"
                        />
                    </svg>
                    Entrar a la Granja
                </button>

                <button
                    @click="openRegisterForm"
                    class="group font-fredoka flex transform items-center gap-3 rounded-2xl bg-gradient-to-r from-amber-500 to-orange-500 px-8 py-4 text-lg font-bold text-white shadow-xl transition-all duration-300 hover:scale-105 hover:from-amber-600 hover:to-orange-600"
                >
                    <svg
                        class="group-hover:bounce h-6 w-6 transition-transform"
                        fill="currentColor"
                        viewBox="0 0 20 20"
                    >
                        <path
                            d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z"
                        />
                    </svg>
                    Crear Nueva Granja
                </button>
            </div>

            <!-- Escena de granja animada -->
            <div class="relative mb-8 h-96 w-full max-w-4xl">
                <!-- Suelo -->
                <div
                    class="absolute bottom-0 h-32 w-full rounded-t-3xl bg-gradient-to-t from-amber-800 to-amber-600"
                ></div>

                <!-- Plantas animadas -->
                <div
                    class="animate-grow absolute bottom-32 left-1/4 -translate-x-1/2 transform"
                >
                    <svg viewBox="0 0 60 80" class="h-16 w-12">
                        <!-- Tallo -->
                        <rect
                            x="28"
                            y="40"
                            width="4"
                            height="40"
                            fill="#4ade80"
                        />
                        <!-- Hojas -->
                        <ellipse
                            cx="20"
                            cy="50"
                            rx="8"
                            ry="12"
                            fill="#22c55e"
                            transform="rotate(-30 20 50)"
                        />
                        <ellipse
                            cx="40"
                            cy="45"
                            rx="8"
                            ry="12"
                            fill="#22c55e"
                            transform="rotate(30 40 45)"
                        />
                        <!-- Tomate -->
                        <circle cx="30" cy="35" r="8" fill="#ef4444" />
                        <path
                            d="M26 30 Q30 25 34 30"
                            stroke="#22c55e"
                            stroke-width="2"
                            fill="none"
                        />
                    </svg>
                </div>

                <div
                    class="animate-grow-delayed absolute bottom-32 left-2/4 -translate-x-1/2 transform"
                >
                    <svg viewBox="0 0 60 80" class="h-14 w-10">
                        <!-- Tallo -->
                        <rect
                            x="28"
                            y="50"
                            width="4"
                            height="30"
                            fill="#4ade80"
                        />
                        <!-- Hojas de zanahoria -->
                        <path
                            d="M20 50 Q25 40 30 50"
                            stroke="#22c55e"
                            stroke-width="3"
                            fill="none"
                        />
                        <path
                            d="M30 50 Q35 40 40 50"
                            stroke="#22c55e"
                            stroke-width="3"
                            fill="none"
                        />
                        <path
                            d="M25 50 Q30 35 35 50"
                            stroke="#22c55e"
                            stroke-width="3"
                            fill="none"
                        />
                        <!-- Zanahoria -->
                        <path d="M30 50 L26 75 Q30 80 34 75 Z" fill="#f97316" />
                    </svg>
                </div>

                <div
                    class="animate-grow-slow absolute bottom-32 left-3/4 -translate-x-1/2 transform"
                >
                    <svg viewBox="0 0 60 80" class="h-18 w-14">
                        <!-- Tallo -->
                        <rect
                            x="28"
                            y="35"
                            width="4"
                            height="45"
                            fill="#4ade80"
                        />
                        <!-- Hojas de maíz -->
                        <ellipse
                            cx="15"
                            cy="45"
                            rx="6"
                            ry="20"
                            fill="#22c55e"
                            transform="rotate(-20 15 45)"
                        />
                        <ellipse
                            cx="45"
                            cy="40"
                            rx="6"
                            ry="20"
                            fill="#22c55e"
                            transform="rotate(20 45 40)"
                        />
                        <!-- Mazorca -->
                        <ellipse
                            cx="30"
                            cy="30"
                            rx="6"
                            ry="15"
                            fill="#fbbf24"
                        />
                        <g fill="#f59e0b">
                            <circle cx="27" cy="25" r="1.5" />
                            <circle cx="33" cy="27" r="1.5" />
                            <circle cx="28" cy="30" r="1.5" />
                            <circle cx="32" cy="32" r="1.5" />
                            <circle cx="29" cy="35" r="1.5" />
                        </g>
                    </svg>
                </div>

                <!-- Tractor animado -->
                <div class="animate-drive absolute right-20 bottom-32">
                    <svg viewBox="0 0 100 60" class="h-12 w-20">
                        <!-- Cuerpo del tractor -->
                        <rect
                            x="20"
                            y="25"
                            width="50"
                            height="20"
                            fill="#dc2626"
                            rx="5"
                        />
                        <!-- Cabina -->
                        <rect
                            x="45"
                            y="15"
                            width="20"
                            height="15"
                            fill="#dc2626"
                            rx="3"
                        />
                        <!-- Ventana -->
                        <rect
                            x="47"
                            y="17"
                            width="16"
                            height="8"
                            fill="#93c5fd"
                            rx="2"
                        />
                        <!-- Ruedas -->
                        <circle cx="30" cy="50" r="8" fill="#374151" />
                        <circle cx="30" cy="50" r="5" fill="#6b7280" />
                        <circle cx="65" cy="50" r="12" fill="#374151" />
                        <circle cx="65" cy="50" r="8" fill="#6b7280" />
                        <!-- Chimenea -->
                        <rect
                            x="25"
                            y="15"
                            width="3"
                            height="10"
                            fill="#374151"
                        />
                        <!-- Humo -->
                        <circle
                            cx="26.5"
                            cy="12"
                            r="2"
                            fill="#d1d5db"
                            opacity="0.7"
                        />
                        <circle
                            cx="28"
                            cy="8"
                            r="1.5"
                            fill="#d1d5db"
                            opacity="0.5"
                        />
                    </svg>
                </div>

                <!-- Casa de granja -->
                <div class="animate-bounce-gentle absolute bottom-32 left-10">
                    <svg viewBox="0 0 80 80" class="h-16 w-16">
                        <!-- Base de la casa -->
                        <rect
                            x="15"
                            y="40"
                            width="50"
                            height="35"
                            fill="#dc2626"
                        />
                        <!-- Techo -->
                        <polygon points="10,40 40,15 70,40" fill="#7c2d12" />
                        <!-- Puerta -->
                        <rect
                            x="30"
                            y="55"
                            width="12"
                            height="20"
                            fill="#92400e"
                        />
                        <!-- Ventanas -->
                        <rect
                            x="20"
                            y="45"
                            width="8"
                            height="8"
                            fill="#fbbf24"
                        />
                        <rect
                            x="52"
                            y="45"
                            width="8"
                            height="8"
                            fill="#fbbf24"
                        />
                        <!-- Chimenea -->
                        <rect
                            x="50"
                            y="20"
                            width="6"
                            height="15"
                            fill="#7c2d12"
                        />
                        <!-- Humo de la chimenea -->
                        <circle
                            cx="53"
                            cy="17"
                            r="2"
                            fill="#d1d5db"
                            opacity="0.7"
                        />
                        <circle
                            cx="55"
                            cy="13"
                            r="1.5"
                            fill="#d1d5db"
                            opacity="0.5"
                        />
                    </svg>
                </div>

                <!-- Animales de granja -->
                <div class="animate-walk absolute right-40 bottom-32">
                    <svg viewBox="0 0 60 40" class="h-8 w-12">
                        <!-- Cuerpo de la vaca -->
                        <ellipse
                            cx="30"
                            cy="25"
                            rx="20"
                            ry="10"
                            fill="#f3f4f6"
                        />
                        <!-- Manchas -->
                        <ellipse cx="25" cy="22" rx="4" ry="3" fill="#374151" />
                        <ellipse cx="35" cy="28" rx="3" ry="2" fill="#374151" />
                        <!-- Cabeza -->
                        <ellipse cx="15" cy="20" rx="8" ry="6" fill="#f3f4f6" />
                        <!-- Cuernos -->
                        <path
                            d="M10 16 L8 12"
                            stroke="#fbbf24"
                            stroke-width="2"
                        />
                        <path
                            d="M20 16 L22 12"
                            stroke="#fbbf24"
                            stroke-width="2"
                        />
                        <!-- Patas -->
                        <rect
                            x="18"
                            y="32"
                            width="3"
                            height="6"
                            fill="#374151"
                        />
                        <rect
                            x="25"
                            y="32"
                            width="3"
                            height="6"
                            fill="#374151"
                        />
                        <rect
                            x="32"
                            y="32"
                            width="3"
                            height="6"
                            fill="#374151"
                        />
                        <rect
                            x="39"
                            y="32"
                            width="3"
                            height="6"
                            fill="#374151"
                        />
                        <!-- Cola -->
                        <path
                            d="M50 25 Q55 20 52 30"
                            stroke="#374151"
                            stroke-width="2"
                            fill="none"
                        />
                    </svg>
                </div>

                <!-- Herramientas de granja -->
                <div
                    class="absolute bottom-20 left-1/2 -translate-x-1/2 transform animate-pulse"
                >
                    <svg viewBox="0 0 100 40" class="h-8 w-20">
                        <!-- Pala -->
                        <rect
                            x="10"
                            y="25"
                            width="3"
                            height="15"
                            fill="#92400e"
                        />
                        <path d="M8 25 L11.5 20 L15 25 Z" fill="#6b7280" />

                        <!-- Rastrillo -->
                        <rect
                            x="30"
                            y="25"
                            width="3"
                            height="15"
                            fill="#92400e"
                        />
                        <rect
                            x="25"
                            y="23"
                            width="13"
                            height="2"
                            fill="#6b7280"
                        />
                        <g fill="#6b7280">
                            <rect x="26" y="20" width="1" height="3" />
                            <rect x="28" y="20" width="1" height="3" />
                            <rect x="30" y="20" width="1" height="3" />
                            <rect x="32" y="20" width="1" height="3" />
                            <rect x="34" y="20" width="1" height="3" />
                            <rect x="36" y="20" width="1" height="3" />
                        </g>

                        <!-- Regadera -->
                        <ellipse cx="60" cy="30" rx="8" ry="5" fill="#22c55e" />
                        <rect
                            x="68"
                            y="28"
                            width="8"
                            height="4"
                            fill="#22c55e"
                        />
                        <circle cx="76" cy="30" r="2" fill="#22c55e" />
                        <rect
                            x="58"
                            y="25"
                            width="4"
                            height="3"
                            fill="#22c55e"
                        />
                        <!-- Agua -->
                        <g fill="#3b82f6" opacity="0.7">
                            <circle cx="78" cy="32" r="0.5" />
                            <circle cx="80" cy="34" r="0.5" />
                            <circle cx="79" cy="36" r="0.5" />
                        </g>
                    </svg>
                </div>
            </div>
        </main>

        <!-- Formulario de Login (Izquierda) -->
        <div 
            v-if="showLoginForm"
            class="fixed inset-0 z-50 flex items-center justify-start bg-black bg-opacity-50"
            @click="closeForms"
        >
            <div 
                class="animate-slide-in-left bg-gradient-to-br from-green-50 to-green-100 p-8 shadow-2xl w-full max-w-md h-full overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-fredoka text-3xl font-bold text-green-800">
                        ¡Bienvenido de vuelta!
                    </h2>
                    <button 
                        @click="closeForms"
                        class="p-2 rounded-full hover:bg-green-200 transition-colors"
                    >
                        <X class="h-6 w-6 text-green-700" />
                    </button>
                </div>
                
                <p class="font-fredoka text-green-700 mb-8">
                    Ingresa a tu granja y continúa cultivando tus sueños
                </p>

                <Form
                    v-bind="AuthenticatedSessionController.store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div>
                        <Label for="login-email" class="font-fredoka text-green-800 font-semibold">
                            Correo Electrónico
                        </Label>
                        <Input
                            id="login-email"
                            type="email"
                            name="email"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="email"
                            class="mt-2 border-green-300 focus:border-green-500 focus:ring-green-500 rounded-xl"
                            placeholder="tu@email.com"
                        />
                        <InputError :message="errors.email" class="mt-1" />
                    </div>

                    <div>
                        <Label for="login-password" class="font-fredoka text-green-800 font-semibold">
                            Contraseña
                        </Label>
                        <Input
                            id="login-password"
                            type="password"
                            name="password"
                            required
                            :tabindex="2"
                            autocomplete="current-password"
                            class="mt-2 border-green-300 focus:border-green-500 focus:ring-green-500 rounded-xl"
                            placeholder="Tu contraseña"
                        />
                        <InputError :message="errors.password" class="mt-1" />
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                        class="w-full font-fredoka bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white font-bold py-3 rounded-xl shadow-lg transition-all duration-300 hover:scale-105 disabled:opacity-50"
                    >
                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                        Entrar a la Granja
                    </Button>
                </Form>

                <div class="mt-6 text-center">
                    <p class="font-fredoka text-green-700">
                        ¿No tienes una granja aún?
                        <button 
                            @click="openRegisterForm"
                            class="text-amber-600 hover:text-amber-700 font-bold underline"
                        >
                            Crear Nueva Granja
                        </button>
                    </p>
                </div>
            </div>
        </div>

        <!-- Formulario de Registro (Derecha) -->
        <div 
            v-if="showRegisterForm"
            class="fixed inset-0 z-50 flex items-center justify-end bg-black bg-opacity-50"
            @click="closeForms"
        >
            <div 
                class="animate-slide-in-right bg-gradient-to-br from-amber-50 to-orange-100 p-8 shadow-2xl w-full max-w-md h-full overflow-y-auto"
                @click.stop
            >
                <div class="flex items-center justify-between mb-6">
                    <h2 class="font-fredoka text-3xl font-bold text-amber-800">
                        ¡Crea tu Granja!
                    </h2>
                    <button 
                        @click="closeForms"
                        class="p-2 rounded-full hover:bg-amber-200 transition-colors"
                    >
                        <X class="h-6 w-6 text-amber-700" />
                    </button>
                </div>
                
                <p class="font-fredoka text-amber-700 mb-8">
                    Comienza tu aventura agrícola y construye el imperio de tus sueños
                </p>

                <Form
                    v-bind="RegisteredUserController.store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div>
                        <Label for="register-name" class="font-fredoka text-amber-800 font-semibold">
                            Nombre del Granjero
                        </Label>
                        <Input
                            id="register-name"
                            type="text"
                            name="name"
                            required
                            autofocus
                            :tabindex="1"
                            autocomplete="name"
                            class="mt-2 border-amber-300 focus:border-amber-500 focus:ring-amber-500 rounded-xl"
                            placeholder="Tu nombre completo"
                        />
                        <InputError :message="errors.name" class="mt-1" />
                    </div>

                    <div>
                        <Label for="register-email" class="font-fredoka text-amber-800 font-semibold">
                            Correo Electrónico
                        </Label>
                        <Input
                            id="register-email"
                            type="email"
                            name="email"
                            required
                            :tabindex="2"
                            autocomplete="email"
                            class="mt-2 border-amber-300 focus:border-amber-500 focus:ring-amber-500 rounded-xl"
                            placeholder="tu@email.com"
                        />
                        <InputError :message="errors.email" class="mt-1" />
                    </div>

                    <div>
                        <Label for="register-password" class="font-fredoka text-amber-800 font-semibold">
                            Contraseña
                        </Label>
                        <Input
                            id="register-password"
                            type="password"
                            name="password"
                            required
                            :tabindex="3"
                            autocomplete="new-password"
                            class="mt-2 border-amber-300 focus:border-amber-500 focus:ring-amber-500 rounded-xl"
                            placeholder="Crea una contraseña segura"
                        />
                        <InputError :message="errors.password" class="mt-1" />
                    </div>

                    <div>
                        <Label for="register-password-confirmation" class="font-fredoka text-amber-800 font-semibold">
                            Confirmar Contraseña
                        </Label>
                        <Input
                            id="register-password-confirmation"
                            type="password"
                            name="password_confirmation"
                            required
                            :tabindex="4"
                            autocomplete="new-password"
                            class="mt-2 border-amber-300 focus:border-amber-500 focus:ring-amber-500 rounded-xl"
                            placeholder="Confirma tu contraseña"
                        />
                        <InputError :message="errors.password_confirmation" class="mt-1" />
                    </div>

                    <Button
                        type="submit"
                        :disabled="processing"
                        class="w-full font-fredoka bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-3 rounded-xl shadow-lg transition-all duration-300 hover:scale-105 disabled:opacity-50"
                    >
                        <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                        Crear Mi Granja
                    </Button>
                </Form>

                <div class="mt-6 text-center">
                    <p class="font-fredoka text-amber-700">
                        ¿Ya tienes una granja?
                        <button 
                            @click="openLoginForm"
                            class="text-green-600 hover:text-green-700 font-bold underline"
                        >
                            Entrar a la Granja
                        </button>
                    </p>
                </div>
            </div>
        </div>

        <!-- Elementos decorativos del suelo -->
        <div
            class="absolute bottom-0 h-20 w-full bg-gradient-to-t from-green-600 to-green-400"
        ></div>
        <div class="absolute bottom-0 h-8 w-full bg-green-700"></div>

        <!-- Flores decorativas -->
        <div class="animate-sway absolute bottom-8 left-20">
            <svg viewBox="0 0 20 30" class="h-6 w-4">
                <rect x="9" y="15" width="2" height="15" fill="#22c55e" />
                <circle cx="10" cy="12" r="4" fill="#ec4899" />
                <circle cx="10" cy="12" r="2" fill="#fbbf24" />
            </svg>
        </div>

        <div class="animate-sway-delayed absolute right-32 bottom-8">
            <svg viewBox="0 0 20 30" class="h-6 w-4">
                <rect x="9" y="15" width="2" height="15" fill="#22c55e" />
                <circle cx="10" cy="12" r="4" fill="#3b82f6" />
                <circle cx="10" cy="12" r="2" fill="#fbbf24" />
            </svg>
        </div>
    </div>
</template>

<style scoped>
@import url('https://fonts.googleapis.com/css2?family=Fredoka:wght@300;400;500;600;700&display=swap');

.font-fredoka {
    font-family: 'Fredoka', cursive;
}

/* Animaciones personalizadas */
@keyframes float {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-10px);
    }
}

@keyframes float-delayed {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-8px);
    }
}

@keyframes float-slow {
    0%,
    100% {
        transform: translateY(0px);
    }
    50% {
        transform: translateY(-6px);
    }
}

@keyframes spin-slow {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

@keyframes grow {
    0% {
        transform: translateX(-50%) scale(0);
    }
    50% {
        transform: translateX(-50%) scale(1.1);
    }
    100% {
        transform: translateX(-50%) scale(1);
    }
}

@keyframes grow-delayed {
    0% {
        transform: translateX(-50%) scale(0);
    }
    60% {
        transform: translateX(-50%) scale(0);
    }
    80% {
        transform: translateX(-50%) scale(1.1);
    }
    100% {
        transform: translateX(-50%) scale(1);
    }
}

@keyframes grow-slow {
    0% {
        transform: translateX(-50%) scale(0);
    }
    70% {
        transform: translateX(-50%) scale(0);
    }
    90% {
        transform: translateX(-50%) scale(1.1);
    }
    100% {
        transform: translateX(-50%) scale(1);
    }
}

@keyframes drive {
    0% {
        transform: translateX(0);
    }
    50% {
        transform: translateX(10px);
    }
    100% {
        transform: translateX(0);
    }
}

@keyframes bounce-gentle {
    0%,
    100% {
        transform: translateY(0);
    }
    50% {
        transform: translateY(-5px);
    }
}

@keyframes walk {
    0%,
    100% {
        transform: translateX(0);
    }
    25% {
        transform: translateX(5px);
    }
    75% {
        transform: translateX(-5px);
    }
}

@keyframes fade-in {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-up {
    from {
        opacity: 0;
        transform: translateY(40px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slide-in-left {
    from {
        opacity: 0;
        transform: translateX(-100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes slide-in-right {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}

@keyframes sway {
    0%,
    100% {
        transform: rotate(-2deg);
    }
    50% {
        transform: rotate(2deg);
    }
}

@keyframes sway-delayed {
    0%,
    100% {
        transform: rotate(2deg);
    }
    50% {
        transform: rotate(-2deg);
    }
}

.animate-float {
    animation: float 6s ease-in-out infinite;
}

.animate-float-delayed {
    animation: float-delayed 8s ease-in-out infinite;
}

.animate-float-slow {
    animation: float-slow 10s ease-in-out infinite;
}

.animate-spin-slow {
    animation: spin-slow 20s linear infinite;
}

.animate-grow {
    animation: grow 2s ease-out;
}

.animate-grow-delayed {
    animation: grow-delayed 2s ease-out;
}

.animate-grow-slow {
    animation: grow-slow 2s ease-out;
}

.animate-drive {
    animation: drive 4s ease-in-out infinite;
}

.animate-bounce-gentle {
    animation: bounce-gentle 3s ease-in-out infinite;
}

.animate-walk {
    animation: walk 3s ease-in-out infinite;
}

.animate-fade-in {
    animation: fade-in 1s ease-out;
}

.animate-slide-up {
    animation: slide-up 1s ease-out 0.5s both;
}

.animate-slide-in-left {
    animation: slide-in-left 0.4s ease-out;
}

.animate-slide-in-right {
    animation: slide-in-right 0.4s ease-out;
}

.animate-sway {
    animation: sway 4s ease-in-out infinite;
}

.animate-sway-delayed {
    animation: sway-delayed 5s ease-in-out infinite;
}

/* Efectos de hover mejorados */
.group:hover .group-hover\:rotate-12 {
    transform: rotate(12deg);
}

.group:hover .group-hover\:bounce {
    animation: bounce 0.5s;
}

/* Nubes con pseudo-elementos para mejor apariencia */
.cloud::before {
    content: '';
    position: absolute;
    background: white;
    border-radius: 50%;
}

.cloud-1::before {
    width: 12px;
    height: 12px;
    top: -6px;
    left: 8px;
}

.cloud-2::before {
    width: 10px;
    height: 10px;
    top: -5px;
    left: 6px;
}

.cloud-3::before {
    width: 14px;
    height: 14px;
    top: -7px;
    left: 10px;
}

/* Responsive adjustments */
@media (max-width: 768px) {
    .animate-drive {
        animation: none;
    }

    .animate-walk {
        animation: none;
    }
}
</style>
