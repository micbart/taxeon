const NAVY = 0x080e2e;
const COBALT = 0x2038be;

const VANTA_EFFECTS = {
	fog: {
		load: () => import('vanta/dist/vanta.fog.min.js'),
		options: {
			highlightColor: COBALT,
			midtoneColor: 0x0f1d6e,
			lowlightColor: NAVY,
			baseColor: NAVY,
			blurFactor: 0.6,
			speed: 1.2,
			zoom: 0.8,
		},
	},
	clouds: {
		load: () => import('vanta/dist/vanta.clouds.min.js'),
		options: {
			skyColor: NAVY,
			cloudColor: 0x1530a0,
			cloudShadowColor: 0x050a1e,
			sunColor: COBALT,
			sunGlareColor: 0x1228a0,
			sunlightColor: 0x1838c8,
			speed: 0.8,
		},
	},
	cells: {
		load: () => import('vanta/dist/vanta.cells.min.js'),
		options: {
			scale: 1.0,
			color1: NAVY,
			color2: COBALT,
			size: 1.8,
			speed: 0.9,
		},
	},
	net: {
		load: () => import('vanta/dist/vanta.net.min.js'),
		options: {
			scale: 1.0,
			scaleMobile: 1.0,
			color: 0x2848d0,
			backgroundColor: NAVY,
			points: 10.0,
			maxDistance: 22.0,
			spacing: 18.0,
			showDots: true,
		},
	},
	birds: {
		load: () => import('vanta/dist/vanta.birds.min.js'),
		options: {
			scale: 1.0,
			scaleMobile: 1.0,
			backgroundColor: NAVY,
			color1: COBALT,
			color2: 0x1228a0,
			colorMode: 'variance',
			birdSize: 1.2,
			wingSpan: 28.0,
			speedLimit: 4.0,
			separation: 40.0,
			alignment: 30.0,
			cohesion: 30.0,
			quantity: 3.0,
		},
	},
};

export function initHeroBackground() {
	const host = document.querySelector('[data-hero-bg]');

	if (!host) {
		return;
	}

	const effect = host.dataset.heroBg;

	if (!effect || effect === 'none') {
		return;
	}

	const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
	const savesData = navigator.connection?.saveData === true;

	if (prefersReduced || savesData) {
		return;
	}

	if (effect === 'bands') {
		startBands(host);
		return;
	}

	startVanta(host, effect);
}

async function startVanta(host, name) {
	const preset = VANTA_EFFECTS[name];

	if (!preset) {
		return;
	}

	try {
		const [{ default: factory }, THREE] = await Promise.all([preset.load(), import('three')]);

		const instance = factory({
			el: host,
			THREE,
			mouseControls: true,
			touchControls: true,
			gyroControls: false,
			minHeight: 200.0,
			minWidth: 200.0,
			...preset.options,
		});

		pauseOffscreen(host, instance);
	} catch {
	}
}

function pauseOffscreen(host, instance) {
	if (!instance || !('IntersectionObserver' in window)) {
		return;
	}

	const observer = new IntersectionObserver(
		(entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					instance.play?.();
				} else {
					instance.pause?.();
				}
			});
		},
		{ threshold: 0 }
	);

	observer.observe(host);
}

const FRAGMENT_SHADER = `
precision highp float;
uniform float u_time;
uniform vec2 u_res;

float hash(vec2 p) { return fract(sin(dot(p, vec2(127.1, 311.7))) * 43758.5453); }

float bandWidth(int i) {
	if (i == 0) return 0.018;
	if (i == 1) return 0.011;
	if (i == 2) return 0.024;
	if (i == 3) return 0.008;
	if (i == 4) return 0.015;
	if (i == 5) return 0.020;
	if (i == 6) return 0.010;
	return 0.017;
}

void main() {
	vec2 uv = gl_FragCoord.xy / u_res.xy;
	vec3 topCol = vec3(0.031, 0.055, 0.180);
	vec3 botCol = vec3(0.125, 0.220, 0.745);
	float gy = pow(clamp(uv.y, 0.0, 1.0), 0.65);
	vec3 col = mix(botCol, topCol, gy);

	float t = u_time;
	float spd = 0.08;
	vec3 coreCol = vec3(0.22, 0.38, 0.82);
	vec3 glowCol = vec3(0.16, 0.28, 0.68);

	for (int i = 0; i < 8; i++) {
		float fi = float(i);
		float speed = spd;
		float m = mod(fi, 3.0);
		if (m > 0.5 && m < 1.5) speed = spd * 0.7;
		else if (m > 1.5) speed = spd * 1.3;

		float offset = fi * 0.13 + t * speed;
		float proj = (uv.x + uv.y) * 0.7071 + offset;
		float d = abs(fract(proj + 0.5) - 0.5);
		float w = bandWidth(i);
		float core = exp(-d * d / (w * w));
		float blur = w * 5.0;
		float wing = exp(-d * d / (blur * blur)) * 0.35;
		col = mix(col, coreCol, core * 0.18);
		col += glowCol * wing * 0.18;
	}

	float aspect = u_res.x / u_res.y;
	vec2 c1 = vec2(0.5 + 0.38 * sin(t * 0.18), 0.5 + 0.25 * sin(t * 0.13 + 1.3));
	vec2 c2 = vec2(0.5 + 0.35 * sin(t * 0.15 + 2.1), 0.5 + 0.28 * sin(t * 0.11 + 0.7));
	float d1 = length((uv - c1) * vec2(aspect, 1.0));
	float d2 = length((uv - c2) * vec2(aspect, 1.0));
	col += glowCol * exp(-d1 * d1 / (0.30 * 0.30)) * 0.10;
	col += glowCol * exp(-d2 * d2 / (0.20 * 0.20)) * 0.07;

	col *= 1.0 - 0.55 * pow(length(uv - 0.5) * 1.35, 2.0);
	col += (hash(gl_FragCoord.xy + fract(t)) * 2.0 - 1.0) * 0.03;

	gl_FragColor = vec4(col, 1.0);
}
`;

const VERTEX_SHADER = `
attribute vec2 a_pos;
void main() { gl_Position = vec4(a_pos, 0.0, 1.0); }
`;

function compileShader(gl, type, source) {
	const shader = gl.createShader(type);

	gl.shaderSource(shader, source);
	gl.compileShader(shader);

	return gl.getShaderParameter(shader, gl.COMPILE_STATUS) ? shader : null;
}

function startBands(host) {
	const canvas = document.createElement('canvas');

	canvas.className = 'absolute inset-0 size-full';
	canvas.setAttribute('aria-hidden', 'true');
	host.prepend(canvas);

	const gl = canvas.getContext('webgl') || canvas.getContext('experimental-webgl');

	if (!gl) {
		canvas.remove();
		return;
	}

	const vertex = compileShader(gl, gl.VERTEX_SHADER, VERTEX_SHADER);
	const fragment = compileShader(gl, gl.FRAGMENT_SHADER, FRAGMENT_SHADER);

	if (!vertex || !fragment) {
		canvas.remove();
		return;
	}

	const program = gl.createProgram();

	gl.attachShader(program, vertex);
	gl.attachShader(program, fragment);
	gl.linkProgram(program);

	if (!gl.getProgramParameter(program, gl.LINK_STATUS)) {
		canvas.remove();
		return;
	}

	gl.useProgram(program);

	gl.bindBuffer(gl.ARRAY_BUFFER, gl.createBuffer());
	gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1, -1, 3, -1, -1, 3]), gl.STATIC_DRAW);

	const position = gl.getAttribLocation(program, 'a_pos');

	gl.enableVertexAttribArray(position);
	gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);

	const uniformTime = gl.getUniformLocation(program, 'u_time');
	const uniformRes = gl.getUniformLocation(program, 'u_res');
	const start = performance.now();

	let running = true;

	const frame = () => {
		if (!running) {
			return;
		}

		const dpr = Math.min(window.devicePixelRatio || 1, 2);
		const width = Math.max(1, Math.floor(host.clientWidth * dpr));
		const height = Math.max(1, Math.floor(host.clientHeight * dpr));

		if (canvas.width !== width || canvas.height !== height) {
			canvas.width = width;
			canvas.height = height;
			gl.viewport(0, 0, width, height);
		}

		gl.uniform1f(uniformTime, (performance.now() - start) / 1000);
		gl.uniform2f(uniformRes, width, height);
		gl.drawArrays(gl.TRIANGLES, 0, 3);

		requestAnimationFrame(frame);
	};

	requestAnimationFrame(frame);

	if ('IntersectionObserver' in window) {
		new IntersectionObserver(
			(entries) => {
				entries.forEach((entry) => {
					if (entry.isIntersecting && !running) {
						running = true;
						requestAnimationFrame(frame);
					} else if (!entry.isIntersecting) {
						running = false;
					}
				});
			},
			{ threshold: 0 }
		).observe(host);
	}
}
