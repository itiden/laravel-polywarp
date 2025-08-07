# Polywarp

Keep your translations in sync between your laravel backend and frontend.

Fully typed.

## Installation

```sh
composer require itiden/laravel-polywarp
```

And add the vite plugin in your vite config:

```ts
import { polywarp } from "./vendor/itiden/laravel-polywarp/vite-plugin/vite-plugin-polywarp";

export default defineConfig({
  plugins: [
    // ...
    polywarp(),
  ],
});
```

## Usage

### Detecting what language to use

Polywarp will use the lang set on the html tag of your application to determine which language to use:

```html
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
  <!-- <html lang="en"> -->
</html>
```

### scripting

By default, polywarp will generate a `translations.ts` file in your `resources/js` directory. (you can change this in the config file)

This file will contain all the types for your translations, and a `t` function to access them:

```ts
import { t } from "./translations";

console.log(t("welcome")); // "Welcome to our application"
```

Only the translations keys that are used in your frontend code will be included in the generated file, so you can safely use it without worrying about performance.
