const zaokr = (wartosc, miejsca = 0) => {
	const mnoznik = 10 ** miejsca;
	const x = Number((wartosc * mnoznik).toPrecision(15));

	return (x < 0 ? -Math.round(-x) : Math.round(x)) / mnoznik;
};

const zl = (wartosc) => {
	const [calosc, reszta] = Math.abs(wartosc).toFixed(2).split('.');

	return (wartosc < 0 ? '-' : '') + calosc.replace(/\B(?=(\d{3})+$)/g, ' ') + ',' + reszta;
};

function policzPodatkowy(u, przychodMies, kosztyMies, spoleczneMies, stawkaRyczaltu) {
	const przychod = przychodMies * 12;
	const koszty = kosztyMies * 12;
	const spoleczne = spoleczneMies * 12;

	const dochod = przychod - koszty - spoleczne;
	const podstawa = zaokr(dochod);

	const nizsza = u.skalaStawkaNizsza / 100;
	const wyzsza = u.skalaStawkaWyzsza / 100;
	const podatekWProgu = u.skalaProg * nizsza - u.kwotaZmniejszajaca;

	const pdofSkala = Math.max(0, podstawa <= u.skalaProg
		? zaokr(podstawa * nizsza - u.kwotaZmniejszajaca)
		: zaokr((podstawa - u.skalaProg) * wyzsza + podatekWProgu));

	const minimalnaRoczna = zaokr(u.zdrowotnaMinimalna * 12 * (u.zdrowotnaSkala / 100), 2);

	const zdrowotnaSkala = dochod / 12 >= u.zdrowotnaMinimalna
		? zaokr(dochod * (u.zdrowotnaSkala / 100), 2)
		: minimalnaRoczna;

	const pdofLiniowy = Math.max(0, zaokr(podstawa * (u.liniowyStawka / 100)));

	const zdrowotnaLiniowy = (dochod / 12) * (u.zdrowotnaLiniowy / 100) >= u.zdrowotnaMinimalna * (u.zdrowotnaSkala / 100)
		? zaokr(dochod * (u.zdrowotnaLiniowy / 100), 2)
		: minimalnaRoczna;

	const podstawaZdrowotnej = przychod - spoleczne;
	const stawkaZdrowotnej = u.zdrowotnaSkala / 100;

	let zdrowotnaRyczalt;

	if (podstawaZdrowotnej <= u.ryczaltProgNizszy) {
		zdrowotnaRyczalt = zaokr(zaokr(u.ryczaltPrzecietne * 0.6 * 12, 2) * stawkaZdrowotnej, 2);
	} else if (podstawaZdrowotnej <= u.ryczaltProgWyzszy) {
		zdrowotnaRyczalt = zaokr(u.ryczaltPrzecietne * stawkaZdrowotnej * 12, 2);
	} else {
		zdrowotnaRyczalt = zaokr(zaokr(u.ryczaltPrzecietne * 1.8 * 12, 2) * stawkaZdrowotnej, 2);
	}

	const odliczenie = spoleczne + zdrowotnaRyczalt * (u.ryczaltOdliczenie / 100);
	const podstawaRyczaltu = zaokr(przychod - zaokr(odliczenie, 2));
	const pdofRyczalt = Math.max(0, zaokr(zaokr(podstawaRyczaltu * (stawkaRyczaltu / 100), 2)));

	const forma = (pdof, zdrowotna) => {
		const suma = spoleczne + pdof + zdrowotna;

		return { pdof, zdrowotna, spoleczne, suma, naReke: przychod - suma - koszty };
	};

	const formy = {
		skala: forma(pdofSkala, zdrowotnaSkala),
		liniowy: forma(pdofLiniowy, zdrowotnaLiniowy),
		ryczalt: forma(pdofRyczalt, zdrowotnaRyczalt),
	};

	let najlepsza = 'skala';

	Object.entries(formy).forEach(([klucz, dane]) => {
		if (dane.naReke > formy[najlepsza].naReke) {
			najlepsza = klucz;
		}
	});

	return { formy, najlepsza };
}

function kalkulatorPodatkowy(korzen) {
	const ustawienia = JSON.parse(korzen.dataset.ustawienia);
	const pola = [...korzen.querySelectorAll('[data-k-pole]')];
	const liczba = (nazwa) => {
		const pole = pola.find((p) => p.dataset.kPole === nazwa);

		return pole ? parseFloat(String(pole.value).replace(',', '.')) || 0 : 0;
	};

	const przelicz = () => {
		const wynik = policzPodatkowy(
			ustawienia,
			liczba('przychod'),
			liczba('koszty'),
			liczba('spoleczne'),
			liczba('stawka')
		);

		Object.entries(wynik.formy).forEach(([klucz, dane]) => {
			Object.entries(dane).forEach(([pole, wartosc]) => {
				const cel = korzen.querySelector(`[data-k-wynik="${klucz}.${pole}"]`);

				if (cel) {
					cel.textContent = zl(wartosc);
				}
			});

			const miesiecznie = korzen.querySelector(`[data-k-wynik="${klucz}.naRekeMies"]`);

			if (miesiecznie) {
				miesiecznie.textContent = zl(dane.naReke / 12);
			}

			const karta = korzen.querySelector(`[data-k-forma="${klucz}"]`);

			if (karta) {
				karta.toggleAttribute('data-najlepsza', klucz === wynik.najlepsza);
				karta.querySelector('[data-k-znacznik]')?.toggleAttribute('data-widoczny', klucz === wynik.najlepsza);
			}
		});
	};

	pola.forEach((pole) => {
		pole.addEventListener('input', przelicz);
		pole.addEventListener('change', przelicz);
	});

	przelicz();
}

function kalkulatorLimit(korzen) {
	const limitRoczny = parseFloat(korzen.dataset.limit) || 0;
	const pole = korzen.querySelector('[data-k-pole="data"]');

	const przelicz = () => {
		const dzien = new Date(pole.value + 'T00:00:00');

		if (Number.isNaN(dzien.getTime())) {
			return;
		}

		const rok = dzien.getFullYear();
		const koniec = new Date(rok, 11, 31);
		const poczatek = new Date(rok, 0, 1);
		const doba = 24 * 60 * 60 * 1000;

		const dni = Math.round((koniec - dzien) / doba) + 1;
		const dniWRoku = Math.round((koniec - poczatek) / doba) + 1;

		const wyniki = {
			dni: String(dni),
			limit: zl((limitRoczny * dni) / dniWRoku),
			rok: String(rok),
			data: pole.value,
		};

		Object.entries(wyniki).forEach(([klucz, wartosc]) => {
			const cel = korzen.querySelector(`[data-k-wynik="${klucz}"]`);

			if (cel) {
				cel.textContent = wartosc;
			}
		});
	};

	pole.addEventListener('change', przelicz);
	pole.addEventListener('input', przelicz);
}

function kalkulatorKsiegowy(korzen) {
	const karty = [...korzen.querySelectorAll('[data-k-pakiet]')];
	const grupaFormy = korzen.querySelector('[data-k-grupa="forma"]');
	const grupaVat = korzen.querySelector('[data-k-grupa="vat"]');

	const wartosc = (nazwa) => {
		const pola = [...korzen.querySelectorAll(`[data-k-pole="${nazwa}"]`)];
		const zaznaczone = pola.find((p) => p.checked);

		return pola.length && pola[0].type === 'checkbox' ? Boolean(zaznaczone) : zaznaczone?.value;
	};

	const przelicz = () => {
		const samodzielnie = wartosc('obsluga') === 'samodzielnie';
		const ryczalt = wartosc('forma') === 'ryczalt';
		const vat = wartosc('vat');

		grupaFormy.hidden = samodzielnie;
		grupaVat.hidden = samodzielnie || !ryczalt;

		let szukana = 'aplikacja';

		if (!samodzielnie) {
			szukana = ryczalt ? (vat ? 'ryczałt + vat' : 'ryczałt') : 'kpir';
		}

		karty.forEach((karta) => {
			const pasuje = karta.dataset.etykieta.trim().toLowerCase() === szukana;

			karta.toggleAttribute('data-wybrany', pasuje);
			karta.querySelector('[data-k-znacznik]')?.toggleAttribute('data-widoczny', pasuje);
		});
	};

	korzen.querySelectorAll('[data-k-pole]').forEach((pole) => pole.addEventListener('change', przelicz));

	przelicz();
}

export function initKalkulatory(root = document) {
	root.querySelectorAll('[data-kalkulator="podatkowy"]').forEach(kalkulatorPodatkowy);
	root.querySelectorAll('[data-kalkulator="limit"]').forEach(kalkulatorLimit);
	root.querySelectorAll('[data-kalkulator="ksiegowy"]').forEach(kalkulatorKsiegowy);
}

export { policzPodatkowy };
