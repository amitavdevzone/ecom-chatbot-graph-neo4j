@use('Illuminate\Support\Str')

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Recommendations — {{ config('app.name', 'Laravel') }}</title>
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased">
        <main class="mx-auto max-w-3xl px-4 py-10">
            <header class="mb-8">
                <h1 class="text-2xl font-semibold tracking-tight">Recommendation assistant</h1>
                <p class="mt-2 text-sm text-zinc-600">
                    Pick a customer, ask a question, and the agent will use Neo4j graph tools to answer.
                </p>
            </header>

            @if ($error)
                <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    {{ $error }}
                </div>
            @endif

            @if ($errors->any())
                <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900" role="alert">
                    <ul class="list-disc space-y-1 pl-5">
                        @foreach ($errors->all() as $message)
                            <li>{{ $message }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="post"
                action="{{ route('recommendations.store') }}"
                class="space-y-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm"
            >
                @csrf

                <div>
                    <label for="customer_id" class="mb-1 block text-sm font-medium text-zinc-700">Customer</label>
                    <select
                        id="customer_id"
                        name="customer_id"
                        required
                        class="w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-200"
                    >
                        <option value="">Select a customer…</option>
                        @foreach ($customers as $customer)
                            <option
                                value="{{ $customer->id }}"
                                @selected((string) $customer->id === (string) $customerId)
                            >
                                {{ $customer->name }} (ID {{ $customer->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="question" class="mb-1 block text-sm font-medium text-zinc-700">Your question</label>
                    <textarea
                        id="question"
                        name="question"
                        rows="4"
                        required
                        placeholder="What should I recommend based on their purchase history?"
                        class="w-full rounded-lg border border-zinc-300 px-3 py-2 text-sm shadow-sm focus:border-zinc-500 focus:outline-none focus:ring-2 focus:ring-zinc-200"
                    >{{ $question }}</textarea>
                </div>

                <button
                    type="submit"
                    class="inline-flex items-center rounded-lg bg-zinc-900 px-4 py-2 text-sm font-medium text-black hover:bg-zinc-800 focus:outline-none focus:ring-2 focus:ring-zinc-400 focus:ring-offset-2"
                >
                    Ask assistant
                </button>
            </form>

            @if ($answer)
                <section class="mt-8 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm">
                    <h2 class="text-sm font-semibold uppercase tracking-wide text-zinc-500">Assistant response</h2>
                    <div class="assistant-markdown mt-4 text-sm leading-relaxed text-zinc-800">
                        {!! Str::markdown($answer, [
                            'html_input' => 'strip',
                            'allow_unsafe_links' => false,
                        ]) !!}
                    </div>
                </section>
            @endif
        </main>
    </body>
</html>
