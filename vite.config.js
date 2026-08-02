import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import { bunny } from "laravel-vite-plugin/fonts";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
	plugins: [
		laravel({
			input: ["resources/css/app.css", "resources/js/app.js"],
			refresh: true,
			fonts: [
				bunny("Instrument Sans", {
					weights: [400, 500, 600],
				}),
			],
		}),
		tailwindcss(),
	],
	build: {
		// Prevents generating preload tags that trigger browser "preloaded but not used" warnings
		modulePreload: false,
	},
	server: {
		cors: true,
		watch: {
			ignored: ["**/storage/framework/views/**"],
		},
	},
});
