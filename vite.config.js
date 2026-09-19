import { defineConfig } from 'vite';
import tailwindcss from '@tailwindcss/vite';
import { resolve, dirname } from 'path';
import { fileURLToPath } from 'url';
import { readdirSync, writeFileSync, rmSync, mkdirSync, existsSync, cpSync } from 'fs';

const root = dirname(fileURLToPath(import.meta.url));

const input = {
	main: resolve(root, 'src/js/main.js'),
	blog: resolve(root, 'src/js/blog.js'),
	'single-post': resolve(root, 'src/js/single-post.js'),
	admin: resolve(root, 'src/js/admin.js'),
	'editor-canvas': resolve(root, 'src/scss/editor-canvas.scss'),
};

const blocksDir = resolve(root, 'src/blocks');

if (existsSync(blocksDir)) {
	for (const entry of readdirSync(blocksDir, { withFileTypes: true })) {
		if (!entry.isDirectory()) {
			continue;
		}

		const name = entry.name;

		for (const [ext, prefix] of [['.js', 'block-js'], ['.scss', 'block-css'], ['-editor.scss', 'block-editor-css']]) {
			const file = resolve(blocksDir, name, `${name}${ext}`);

			if (existsSync(file)) {
				input[`${prefix}-${name}`] = file;
			}
		}
	}
}

function wpHotFile() {
	const hotFile = resolve(root, 'dist/.hot');

	const clean = () => {
		try {
			rmSync(hotFile);
		} catch {
		}
	};

	return {
		name: 'wp-hot-file',
		configureServer(server) {
			server.httpServer?.once('listening', () => {
				const address = server.httpServer.address();
				const protocol = server.config.server.https ? 'https' : 'http';
				const host = typeof address === 'object' && address ? `localhost:${address.port}` : 'localhost:5173';
				mkdirSync(resolve(root, 'dist'), { recursive: true });
				writeFileSync(hotFile, `${protocol}://${host}`);
			});

			process.once('exit', clean);
			process.once('SIGINT', () => process.exit());
			process.once('SIGTERM', () => process.exit());
		},
	};
}

function wpPhpTwigReload() {
	return {
		name: 'wp-php-twig-reload',
		configureServer(server) {
			server.watcher.add([
				resolve(root, 'views/**/*.twig'),
				resolve(root, 'app/**/*.php'),
				resolve(root, '*.php'),
			]);
			server.watcher.on('change', (file) => {
				if (file.endsWith('.twig') || file.endsWith('.php')) {
					server.ws.send({ type: 'full-reload' });
				}
			});
		},
	};
}

function copySvg() {
	return {
		name: 'wp-copy-svg',
		apply: 'build',
		closeBundle() {
			const svgDir = resolve(root, 'src/svg');

			if (existsSync(svgDir)) {
				cpSync(svgDir, resolve(root, 'dist/svg'), { recursive: true });
			}
		},
	};
}

export default defineConfig({
	plugins: [tailwindcss(), wpHotFile(), wpPhpTwigReload(), copySvg()],
	base: './',
	server: {
		cors: true,
		strictPort: false,
		port: 5173,
		origin: 'http://localhost:5173',
		open: 'http://localhost:8888/taxeon/',
	},
	build: {
		outDir: 'dist',
		manifest: true,
		emptyOutDir: true,
		rollupOptions: {
			input,
			output: {
				assetFileNames: (assetInfo) => {
					if (/\.(woff2?|ttf|eot)(\?.*)?$/.test(assetInfo.name ?? '')) {
						return 'fonts/[name][extname]';
					}
					return 'assets/[name]-[hash][extname]';
				},
			},
		},
	},
});
