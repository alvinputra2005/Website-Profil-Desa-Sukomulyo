# Instruksi Codex — Layout Khusus Struktur Pemerintahan dengan AntV G6

## Status Dokumen

Dokumen ini merupakan **sumber utama untuk bentuk dan posisi visual struktur pemerintahan** pada halaman:

```text
/pemerintahan-desa
```

Target branch:

```text
baru
```

Library diagram:

```text
@antv/g6 v5
```

Dokumen ini melengkapi arsitektur struktur pemerintahan yang sudah ada. Arsitektur Laravel, sumber data, nama perangkat, jabatan, dan susunan organisasi tidak diubah. Bagian yang ditegaskan di sini adalah **layout visual khusus** agar hasilnya mengikuti gambar referensi.

---

# 1. Perintah Utama untuk Codex

Implementasikan struktur Pemerintah Desa Sukomulyo menggunakan AntV G6 dengan ketentuan berikut:

1. Kepala Desa berada di tengah atas.
2. Tiga Kasi berada di sisi kiri dan tersusun vertikal.
3. Sekretaris Desa berada di kanan atas.
4. Tiga Kaur berada di sisi kanan, di bawah Sekretaris Desa, dan tersusun vertikal.
5. Lima Kasun berada pada satu baris horizontal di bagian bawah.
6. Garis organisasi harus menyatu, siku-siku, dan tidak terputus.
7. Node dan edge harus berada di dalam satu engine AntV G6.
8. Jangan menggunakan tree layout generik sebagai hasil akhir.
9. Jangan membiarkan Dagre mengubah susunan khusus ini.
10. Gunakan posisi referensi tetap dalam ruang koordinat virtual.
11. Gunakan virtual routing nodes untuk membentuk garis cabang bersama.
12. Pada layar kecil, pertahankan susunan yang sama melalui pan, zoom, dan fit view.
13. Jangan mengubah layout menjadi daftar vertikal pada ponsel.
14. Jangan membuat garis menggunakan `<span>`, `border`, atau pseudo-element CSS.
15. Jangan menambahkan kartu persegi panjang besar.
16. Node normal hanya menampilkan foto lingkaran dan jabatan.
17. Nama muncul saat hover, fokus keyboard, atau ketukan.
18. Efek hover membesarkan isi node, bukan memindahkan posisi node G6.
19. Desain warna dan tipografi harus mengikuti tema website yang sudah ada.
20. Gambar referensi hanya dipakai untuk posisi struktur, bukan untuk menyalin banner, watermark, warna latar, atau tipografi gambarnya.

---

# 2. Susunan Organisasi yang Tidak Boleh Berubah

```text
                         KEPALA DESA
                        Safiul Anwar, ST
                               │
       ┌───────────────────────┼────────────────────────┐
       │                       │                        │
       │                 SEKRETARIS DESA                │
       │                 Baktiyar Kufain                │
       │                       │                        │
       │                       └──────────────┐         │
       │                                      │         │
KASI PEMERINTAHAN                       KAUR KEUANGAN    │
Angga Saputra                           Suwarno          │
       │                                      │         │
KASI PELAYANAN                          KAUR PERENCANAAN  │
Wike Priharti Y                         Reza Tri Purnomo │
       │                                      │         │
KASI KESEJAHTERAAN                     KAUR TU & UMUM    │
Mohamad Sholeh                          Catur Yulianto   │
                                                      │
                         ───────────────────────────────
                         │        │        │       │       │
                    KASUN     KASUN    KASUN   KASUN   KASUN
                    BAKIR     BIYAN    CUMUL   KEDUNG  TALASAN
```

Representasi kelompok:

```text
TENGAH ATAS
└── 1 Kepala Desa

SISI KIRI
├── 1 Kasi Pemerintahan
├── 1 Kasi Pelayanan
└── 1 Kasi Kesejahteraan

SISI KANAN
├── 1 Sekretaris Desa
│   ├── 1 Kaur Keuangan
│   ├── 1 Kaur Perencanaan
│   └── 1 Kaur TU & Umum

BAGIAN BAWAH
├── 1 Kasun Bakir
├── 1 Kasun Biyan
├── 1 Kasun Cumul
├── 1 Kasun Kedungrejo
└── 1 Kasun Talasan
```

Jumlah visible node:

```text
1 Kepala Desa
3 Kasi
1 Sekretaris Desa
3 Kaur
5 Kasun
----------------
13 visible node
```

---

# 3. Nama, Jabatan, dan Kelompok

| Layout key | Nama | Jabatan | Kelompok |
|---|---|---|---|
| `leader` | Safiul Anwar, ST | Kepala Desa | tengah atas |
| `kasi-government` | Angga Saputra | Kasi Pemerintahan | kiri 1 |
| `kasi-service` | Wike Priharti Y | Kasi Pelayanan | kiri 2 |
| `kasi-welfare` | Mohamad Sholeh | Kasi Kesejahteraan | kiri 3 |
| `secretary` | Baktiyar Kufain | Sekretaris Desa | kanan atas |
| `kaur-finance` | Suwarno | Kaur Keuangan | kanan 1 |
| `kaur-planning` | Reza Tri Purnomo | Kaur Perencanaan | kanan 2 |
| `kaur-general` | Catur Yulianto | Kaur TU & Umum | kanan 3 |
| `kasun-bakir` | Bambang S | Kasun Bakir | bawah 1 |
| `kasun-biyan` | Sispanaji | Kasun Biyan | bawah 2 |
| `kasun-cumul` | Nikita F Z | Kasun Cumul | bawah 3 |
| `kasun-kedungrejo` | Fendi Priyo S | Kasun Kedungrejo | bawah 4 |
| `kasun-talasan` | Cahyo Utomo | Kasun Talasan | bawah 5 |

Database tetap menjadi sumber kebenaran untuk nama dan jabatan. `layoutKey` dipakai hanya untuk menentukan posisi visual.

---

# 4. Prinsip Layout AntV G6

## 4.1 Jangan memakai auto-layout generik untuk posisi akhir

Layout ini bersifat asimetris dan mengikuti susunan pemerintahan pada gambar referensi.

Karena itu, jangan menjadikan konfigurasi berikut sebagai layout akhir:

```js
layout: {
    type: 'dagre',
}
```

Dagre dapat dipakai untuk eksperimen, tetapi hasil akhirnya harus menggunakan posisi yang ditentukan oleh layout khusus Sukomulyo.

Gunakan:

```text
fixed reference coordinate layout
+ virtual routing nodes
+ AntV G6 polyline edges
+ orthogonal router
```

## 4.2 Ruang koordinat virtual

Gunakan ukuran ruang kerja tetap:

```js
const REFERENCE_WIDTH = 1440;
const REFERENCE_HEIGHT = 760;
```

Posisi seluruh node ditentukan di dalam ruang ini.

Container browser boleh berubah ukuran, tetapi koordinat graph tetap konsisten. AntV G6 kemudian menampilkan graph menggunakan:

```js
autoFit: 'view'
autoResize: true
drag-canvas
zoom-canvas
```

Dengan cara ini:

- susunan tidak berubah;
- edge tetap terhubung;
- desktop terlihat penuh;
- tablet dan ponsel dapat menggunakan pan dan zoom;
- tidak diperlukan scrollbar horizontal halaman.

---

# 5. Posisi Visible Node

Gunakan posisi berikut sebagai titik pusat node.

## 5.1 Posisi dalam rasio

| Layout key | X ratio | Y ratio |
|---|---:|---:|
| `leader` | `0.50` | `0.12` |
| `kasi-government` | `0.12` | `0.39` |
| `kasi-service` | `0.12` | `0.52` |
| `kasi-welfare` | `0.12` | `0.65` |
| `secretary` | `0.70` | `0.29` |
| `kaur-finance` | `0.87` | `0.50` |
| `kaur-planning` | `0.87` | `0.63` |
| `kaur-general` | `0.87` | `0.76` |
| `kasun-bakir` | `0.20` | `0.91` |
| `kasun-biyan` | `0.35` | `0.91` |
| `kasun-cumul` | `0.50` | `0.91` |
| `kasun-kedungrejo` | `0.65` | `0.91` |
| `kasun-talasan` | `0.80` | `0.91` |

## 5.2 Posisi piksel pada ruang referensi

Dengan ukuran `1440 × 760`:

| Layout key | X | Y |
|---|---:|---:|
| `leader` | `720` | `91` |
| `kasi-government` | `173` | `296` |
| `kasi-service` | `173` | `395` |
| `kasi-welfare` | `173` | `494` |
| `secretary` | `1008` | `220` |
| `kaur-finance` | `1253` | `380` |
| `kaur-planning` | `1253` | `479` |
| `kaur-general` | `1253` | `578` |
| `kasun-bakir` | `288` | `692` |
| `kasun-biyan` | `504` | `692` |
| `kasun-cumul` | `720` | `692` |
| `kasun-kedungrejo` | `936` | `692` |
| `kasun-talasan` | `1152` | `692` |

Posisi dapat disesuaikan maksimal sekitar `±20px` saat finishing, tetapi pola dasarnya tidak boleh berubah.

---

# 6. Peta Visual yang Harus Dihasilkan

```text
x=0                                                           x=1440

                             [ KEPALA DESA ]
                                  720,91
                                     │
       ┌─────────────────────────────┼───────────────────────┐
       │                                                     │
       │                                             [ SEKDES ]
       │                                              1008,220
       │                                                     └──────────┐
       │                                                                │
[ KASI PEMERINTAHAN ]                                         [ KAUR KEUANGAN ]
      173,296                                                       1253,380
       │                                                                │
[ KASI PELAYANAN ]                                            [ KAUR PERENCANAAN ]
      173,395                                                       1253,479
       │                                                                │
[ KASI KESEJAHTERAAN ]                                       [ KAUR TU & UMUM ]
      173,494                                                       1253,578
                                     │
                                     │
            ─────────────────────────┼─────────────────────────
            │              │         │         │               │
      [ KASUN BAKIR ] [ KASUN BIYAN ] [ KASUN CUMUL ]
          288,692        504,692        720,692

                       [ KASUN KEDUNGREJO ] [ KASUN TALASAN ]
                              936,692             1152,692
```

Baris Kasun harus terlihat sebagai satu baris, bukan dua baris.

---

# 7. Virtual Routing Nodes

Visible node tidak cukup untuk membentuk bus line yang sama seperti referensi. Tambahkan invisible routing nodes.

Virtual node:

- tidak memiliki foto;
- tidak memiliki teks;
- tidak menerima fokus;
- `aria-hidden="true"`;
- ukuran sekitar `2px–4px`;
- opacity `0`;
- tetap menjadi source atau target edge;
- tidak termasuk perangkat desa.

## 7.1 Virtual node untuk cabang kiri

```text
hub-top-center
hub-left-top
hub-left-row-1
hub-left-row-2
hub-left-row-3
```

Posisi:

| Routing key | X | Y |
|---|---:|---:|
| `hub-top-center` | `720` | `210` |
| `hub-left-top` | `173` | `210` |
| `hub-left-row-1` | `173` | `296` |
| `hub-left-row-2` | `173` | `395` |
| `hub-left-row-3` | `173` | `494` |

## 7.2 Virtual node untuk cabang kanan

```text
hub-secretary-out
hub-right-top
hub-right-row-1
hub-right-row-2
hub-right-row-3
```

Posisi:

| Routing key | X | Y |
|---|---:|---:|
| `hub-secretary-out` | `1070` | `315` |
| `hub-right-top` | `1253` | `315` |
| `hub-right-row-1` | `1253` | `380` |
| `hub-right-row-2` | `1253` | `479` |
| `hub-right-row-3` | `1253` | `578` |

## 7.3 Virtual node untuk bagian bawah

```text
hub-bottom-center
hub-bottom-1
hub-bottom-2
hub-bottom-3
hub-bottom-4
hub-bottom-5
```

Posisi:

| Routing key | X | Y |
|---|---:|---:|
| `hub-bottom-center` | `720` | `630` |
| `hub-bottom-1` | `288` | `630` |
| `hub-bottom-2` | `504` | `630` |
| `hub-bottom-3` | `720` | `630` |
| `hub-bottom-4` | `936` | `630` |
| `hub-bottom-5` | `1152` | `630` |

`hub-bottom-center` dan `hub-bottom-3` dapat menggunakan posisi yang sama atau dijadikan satu node.

Rekomendasi:

```text
gunakan satu node: hub-bottom-3
```

---

# 8. Topologi Edge Visual

## 8.1 Garis utama dari Kepala Desa

```text
leader
├── hub-top-center
├── secretary
└── hub-bottom-3
```

Namun untuk hasil visual yang lebih bersih, gunakan:

```text
leader -> hub-top-center
hub-top-center -> hub-left-top
hub-top-center -> secretary
leader -> hub-bottom-3
```

## 8.2 Cabang kiri

```text
hub-left-top
└── hub-left-row-1
    └── hub-left-row-2
        └── hub-left-row-3
```

Hubungkan visible node dengan hub yang sejajar:

```text
hub-left-row-1 -> kasi-government
hub-left-row-2 -> kasi-service
hub-left-row-3 -> kasi-welfare
```

Apabila hub dan visible node menggunakan pusat posisi yang sama, pindahkan hub sekitar `40px` ke kanan atau kiri dari node agar edge tidak melintasi isi foto.

Rekomendasi posisi alternatif:

```text
hub kiri x = 245
visible Kasi x = 150
```

Dengan begitu terdapat short horizontal connector dari bus ke setiap Kasi.

## 8.3 Cabang Sekretaris dan Kaur

```text
hub-top-center -> secretary
secretary -> hub-secretary-out
hub-secretary-out -> hub-right-top
hub-right-top -> hub-right-row-1
hub-right-row-1 -> hub-right-row-2
hub-right-row-2 -> hub-right-row-3
```

Hubungkan:

```text
hub-right-row-1 -> kaur-finance
hub-right-row-2 -> kaur-planning
hub-right-row-3 -> kaur-general
```

Jika bus berada tepat pada pusat Kaur, pindahkan bus ke:

```text
x = 1165
```

dan visible Kaur tetap pada:

```text
x = 1253
```

Sehingga garis pendek mengarah horizontal ke node.

## 8.4 Cabang lima Kasun

Trunk utama:

```text
leader -> hub-bottom-3
```

Bus horizontal:

```text
hub-bottom-1
    -> hub-bottom-2
    -> hub-bottom-3
    -> hub-bottom-4
    -> hub-bottom-5
```

Cabang pendek:

```text
hub-bottom-1 -> kasun-bakir
hub-bottom-2 -> kasun-biyan
hub-bottom-3 -> kasun-cumul
hub-bottom-4 -> kasun-kedungrejo
hub-bottom-5 -> kasun-talasan
```

Hasil yang diharapkan:

```text
                 │ dari Kepala Desa
                 │
     ────────────┼────────────────────
     │           │          │          │          │
 Kasun 1      Kasun 2    Kasun 3    Kasun 4    Kasun 5
```

---

# 9. Port Node

Gunakan port tersembunyi agar edge menempel pada sisi yang konsisten.

Visible node memiliki port:

```js
ports: [
    {
        key: 'top',
        placement: 'top',
        visibility: 'hidden',
    },
    {
        key: 'right',
        placement: 'right',
        visibility: 'hidden',
    },
    {
        key: 'bottom',
        placement: 'bottom',
        visibility: 'hidden',
    },
    {
        key: 'left',
        placement: 'left',
        visibility: 'hidden',
    },
]
```

Rekomendasi koneksi:

| Edge | Source port | Target port |
|---|---|---|
| Kepala Desa ke hub atas | `bottom` | `top` |
| Hub atas ke cabang kiri | `left` | `right` |
| Hub atas ke Sekdes | `right` | `top` atau `left` |
| Sekdes ke bus Kaur | `bottom` atau `right` | `left` |
| Bus kiri ke Kasi | `left` | `right` |
| Bus kanan ke Kaur | `right` | `left` |
| Kepala Desa ke bus Kasun | `bottom` | `top` |
| Bus Kasun ke Kasun | `bottom` | `top` |

Edge dapat menetapkan:

```js
style: {
    sourcePort: 'bottom',
    targetPort: 'top',
}
```

Gunakan port yang menghasilkan jalur paling dekat dengan gambar referensi.

---

# 10. Fungsi Penempatan Node

Backend mengirim `layoutKey`. JavaScript menentukan posisi berdasarkan key tersebut.

```js
const REFERENCE_WIDTH = 1440;
const REFERENCE_HEIGHT = 760;

const POSITION_RATIOS = {
    leader: [0.50, 0.12],

    'kasi-government': [0.12, 0.39],
    'kasi-service': [0.12, 0.52],
    'kasi-welfare': [0.12, 0.65],

    secretary: [0.70, 0.29],

    'kaur-finance': [0.87, 0.50],
    'kaur-planning': [0.87, 0.63],
    'kaur-general': [0.87, 0.76],

    'kasun-bakir': [0.20, 0.91],
    'kasun-biyan': [0.35, 0.91],
    'kasun-cumul': [0.50, 0.91],
    'kasun-kedungrejo': [0.65, 0.91],
    'kasun-talasan': [0.80, 0.91],

    'hub-top-center': [0.50, 0.275],

    'hub-left-top': [0.17, 0.275],
    'hub-left-row-1': [0.17, 0.39],
    'hub-left-row-2': [0.17, 0.52],
    'hub-left-row-3': [0.17, 0.65],

    'hub-secretary-out': [0.76, 0.41],
    'hub-right-top': [0.81, 0.41],
    'hub-right-row-1': [0.81, 0.50],
    'hub-right-row-2': [0.81, 0.63],
    'hub-right-row-3': [0.81, 0.76],

    'hub-bottom-1': [0.20, 0.83],
    'hub-bottom-2': [0.35, 0.83],
    'hub-bottom-3': [0.50, 0.83],
    'hub-bottom-4': [0.65, 0.83],
    'hub-bottom-5': [0.80, 0.83],
};

const applySukomulyoReferenceLayout = (graphData) => {
    return {
        ...graphData,

        nodes: graphData.nodes.map((node) => {
            const layoutKey = node.data?.layoutKey;
            const position = POSITION_RATIOS[layoutKey];

            if (!position) {
                throw new Error(
                    `Posisi layout tidak ditemukan: ${layoutKey}`,
                );
            }

            return {
                ...node,

                style: {
                    ...node.style,
                    x: position[0] * REFERENCE_WIDTH,
                    y: position[1] * REFERENCE_HEIGHT,
                },
            };
        }),
    };
};
```

Jangan menentukan posisi berdasarkan urutan array tanpa `layoutKey`, karena perubahan `display_order` dapat menempatkan orang pada kelompok yang salah.

---

# 11. Konfigurasi Graph

Kerangka konfigurasi:

```js
import { Graph } from '@antv/g6';

const positionedData = applySukomulyoReferenceLayout(
    rawGraphData,
);

const graph = new Graph({
    container,
    data: positionedData,

    autoResize: true,

    autoFit: {
        type: 'view',

        options: {
            when: 'always',
            direction: 'both',
        },

        animation: {
            duration: 400,
            easing: 'ease-out',
        },
    },

    padding: [40, 48, 52, 48],

    zoomRange: [0.35, 1.8],

    node: {
        type: 'html',

        style: {
            // Gunakan template HTML node yang sudah dibuat.
        },
    },

    edge: {
        type: 'polyline',

        style: {
            stroke: '#48665a',
            lineWidth: 2.5,
            opacity: 0.92,
            radius: 6,
            endArrow: false,

            router: {
                type: 'orth',
                padding: 10,
            },
        },
    },

    behaviors: [
        {
            type: 'drag-canvas',

            enable: (event) => {
                return event.targetType === 'canvas';
            },
        },
        {
            type: 'zoom-canvas',
        },
    ],
});
```

Tidak perlu mengaktifkan layout Dagre:

```js
// Jangan ditambahkan:
layout: {
    type: 'dagre',
}
```

Posisi sudah tersedia melalui:

```js
node.style.x
node.style.y
```

---

# 12. Kontrak Data Node

Visible node:

```js
{
    id: 'official-1',
    type: 'html',

    data: {
        officialId: 1,
        layoutKey: 'leader',
        name: 'Safiul Anwar, ST',
        role: 'Kepala Desa',
        photo: '/assets/safiul-anwar.jpeg',
        photoAlt: 'Safiul Anwar, ST - Kepala Desa',
        isVirtual: false,
    },
}
```

Virtual routing node:

```js
{
    id: 'routing-hub-left-row-1',
    type: 'html',

    data: {
        layoutKey: 'hub-left-row-1',
        isVirtual: true,
    },

    style: {
        size: [4, 4],
    },
}
```

Edge:

```js
{
    id: 'edge-hub-left-row-1-kasi-government',
    source: 'routing-hub-left-row-1',
    target: 'official-kasi-government',

    type: 'polyline',

    style: {
        sourcePort: 'left',
        targetPort: 'right',
    },
}
```

---

# 13. Node Visual

## 13.1 Kondisi normal

```text
        FOTO LINGKARAN
          JABATAN
```

Nama tidak terlihat pada kondisi awal.

## 13.2 Kondisi hover, fokus, atau aktif

```text
       FOTO LINGKARAN
       sedikit membesar
           JABATAN
      [ NAMA PERANGKAT ]
```

## 13.3 Ukuran

| Node | Diameter foto | Area node |
|---|---:|---:|
| Kepala Desa | `118–126px` | sekitar `190 × 190px` |
| Sekretaris Desa | `104–112px` | sekitar `176 × 180px` |
| Kasi, Kaur, Kasun | `94–104px` | sekitar `164 × 170px` |
| Virtual node | tidak terlihat | `2–4px` |

## 13.4 Larangan

Jangan membuat bentuk seperti:

```text
┌──────────────────────────┐
│ FOTO    NAMA             │
│         JABATAN          │
└──────────────────────────┘
```

Node bukan kartu horizontal.

---

# 14. Hover Tidak Boleh Menggeser Edge

Jangan mengubah `node.style.x`, `node.style.y`, atau ukuran node G6 saat hover.

Gunakan transform pada elemen HTML di dalam node:

```css
.org-node {
    transform: translateY(0) scale(1);
    transition: transform 260ms ease;
}

.org-node:hover,
.org-node:focus-visible,
.org-node.is-active {
    transform: translateY(-6px) scale(1.045);
}
```

Dengan demikian:

- posisi node graph tetap;
- edge tidak ikut bergerak;
- tidak terjadi layout ulang;
- efek tetap terasa seperti node sedang dipilih.

---

# 15. Responsive

## 15.1 Desktop

```text
min-width: 1200px
```

- seluruh struktur terlihat;
- Kepala Desa berada di tengah;
- kiri, kanan, dan bawah memiliki ruang cukup;
- fit view tidak boleh terlalu kecil.

## 15.2 Tablet

```text
768px–1199px
```

- gunakan struktur yang sama;
- render graph dalam ruang virtual `1440 × 760`;
- gunakan fit view saat pertama dibuka;
- pengguna dapat zoom dan pan;
- jangan memindahkan tiga Kasi ke atas atau ke bawah;
- jangan memindahkan lima Kasun menjadi dua baris.

## 15.3 Ponsel

```text
max-width: 767px
```

- struktur tetap sama;
- graph tampil dalam fit view;
- pengguna dapat memperbesar;
- pengguna dapat menggeser canvas;
- jangan mengubahnya menjadi timeline;
- jangan menggunakan overflow horizontal pada halaman;
- tinggi container sekitar `560–640px`.

CSS:

```css
.government-chart {
    width: 100%;
    height: 700px;
}

@media (max-width: 1199px) {
    .government-chart {
        height: 640px;
    }
}

@media (max-width: 767px) {
    .government-chart {
        height: 590px;
    }
}
```

---

# 16. Resize

`autoResize: true` harus diaktifkan.

Tambahkan `ResizeObserver` untuk mengembalikan graph ke fit view setelah container benar-benar berubah ukuran.

```js
let resizeTimer = null;

const resizeObserver = new ResizeObserver(() => {
    window.clearTimeout(resizeTimer);

    resizeTimer = window.setTimeout(() => {
        graph.fitView();
    }, 120);
});

resizeObserver.observe(container);
```

Saat halaman dibongkar:

```js
resizeObserver.disconnect();
graph.destroy();
```

Jangan membuat graph instance baru setiap resize.

---

# 17. Animasi Saat Scroll

Gunakan `IntersectionObserver`.

Urutan yang diharapkan:

```text
1. Kepala Desa muncul.
2. Garis utama atas muncul.
3. Tiga Kasi muncul dari atas ke bawah.
4. Sekretaris Desa muncul.
5. Garis menuju tiga Kaur muncul.
6. Tiga Kaur muncul dari atas ke bawah.
7. Garis vertikal tengah menuju bagian bawah muncul.
8. Bus horizontal Kasun muncul.
9. Lima Kasun muncul dari kiri ke kanan.
```

Implementasi awal boleh memakai:

```js
edge.animation.enter = 'path-in'
node.animation.enter = 'fade'
```

Untuk animasi berurutan yang lebih ketat, gunakan `data.sequence`:

```text
sequence 1 = Kepala Desa
sequence 2 = cabang kiri dan Sekdes
sequence 3 = Kasi dan Kaur
sequence 4 = garis bawah
sequence 5 = Kasun
```

Jangan mengorbankan kestabilan layout hanya untuk animasi. Prioritas:

```text
1. posisi benar;
2. edge benar;
3. responsive benar;
4. interaksi benar;
5. animasi.
```

---

# 18. Warna dan Tampilan

Gunakan warna tema website, bukan menyalin warna peach pada gambar.

Rekomendasi:

```css
--org-ink: #27312a;
--org-green: #526b42;
--org-green-dark: #34462d;
--org-line: #48665a;
--org-paper: #fbf7ef;
--org-border: rgba(63, 84, 70, 0.22);
```

Edge:

```js
stroke: '#48665a'
lineWidth: 2.5
opacity: 0.92
```

Tidak menggunakan:

- garis putus-putus;
- glow;
- neon;
- gradient mencolok;
- animasi memantul;
- kartu besar;
- latar seperti gambar poster.

---

# 19. Data dan Backend

Arsitektur backend yang sudah ada tetap dipertahankan.

Gunakan data:

```php
[
    'id' => $official->id,
    'name' => $official->full_name,
    'role' => $official->position_label,
    'photo' => $official->photo?->url,
    'photo_alt' => $official->photo?->alt_text,
    'superior_id' => $official->superior_id,
    'display_order' => $official->display_order,
    'layout_key' => $resolvedLayoutKey,
]
```

`layout_key` dapat berasal dari `position_code`.

Rekomendasi:

```text
village_head             -> leader
village_secretary        -> secretary
section_government       -> kasi-government
section_service          -> kasi-service
section_welfare          -> kasi-welfare
administrative_finance   -> kaur-finance
administrative_planning  -> kaur-planning
administrative_general   -> kaur-general
hamlet_bakir             -> kasun-bakir
hamlet_biyan             -> kasun-biyan
hamlet_cumul             -> kasun-cumul
hamlet_kedungrejo        -> kasun-kedungrejo
hamlet_talasan           -> kasun-talasan
```

Jangan menentukan layout berdasarkan pencarian nama orang.

Layout harus mengikuti jabatan atau kode posisi agar tetap benar ketika perangkat desa berganti.

---

# 20. File yang Perlu Diperiksa dan Diubah

Codex harus memeriksa branch `baru`, lalu menyesuaikan file yang relevan.

Kemungkinan file:

```text
app/Http/Controllers/SiteController.php
app/Queries/Officials/OfficialOrganizationQuery.php
app/Presenters/OrganizationGraphPresenter.php

resources/views/pages/government.blade.php
resources/views/pages/partials/organization-node.blade.php
resources/views/pages/partials/organization-tree-node.blade.php

resources/js/app.js
resources/js/modules/government-org-chart.js

resources/css/app.css

package.json
package-lock.json

tests/Feature/GovernmentPageTest.php
```

Ketentuan:

1. Hapus penggunaan recursive card tree dari halaman aktif.
2. Jangan menghapus model atau relasi `superior_id`.
3. Hapus CSS lama hanya jika sudah dipastikan tidak digunakan halaman lain.
4. Jangan mengubah route.
5. Jangan mengubah halaman profil desa lain.
6. Pertahankan komentar utama bila masih menjadi requirement.
7. Hapus sidebar khusus struktur jika requirement sebelumnya memang mengharuskannya.
8. Gunakan AntV G6 dari Vite, bukan CDN.

---

# 21. Fallback Foto

Jika foto tersedia:

```text
tampilkan foto lingkaran
```

Jika foto kosong atau gagal dimuat:

```text
tampilkan inisial nama
```

Contoh:

```text
Safiul Anwar, ST -> SA
Baktiyar Kufain  -> BK
Sispanaji        -> S
```

Jangan menampilkan ikon broken image.

---

# 22. Fallback Tanpa JavaScript

Sediakan `<noscript>`:

```blade
<noscript>
    <ul>
        <li>Kepala Desa — Safiul Anwar, ST</li>
        <li>Kasi Pemerintahan — Angga Saputra</li>
        ...
    </ul>
</noscript>
```

Fallback tidak harus mengikuti posisi diagram, tetapi seluruh nama dan jabatan harus tetap dapat dibaca.

---

# 23. Kriteria Penerimaan Layout

Implementasi belum dianggap selesai sebelum seluruh poin berikut terpenuhi.

## 23.1 Posisi

- [ ] Kepala Desa berada di tengah atas.
- [ ] Tiga Kasi berada di sisi kiri.
- [ ] Tiga Kasi tersusun vertikal.
- [ ] Sekretaris Desa berada di kanan atas.
- [ ] Sekretaris Desa tidak berada satu kolom dengan Kasi.
- [ ] Tiga Kaur berada di kanan.
- [ ] Tiga Kaur tersusun vertikal di bawah Sekdes.
- [ ] Lima Kasun berada di bagian bawah.
- [ ] Lima Kasun berada dalam satu baris.
- [ ] Kasun Cumul kurang lebih sejajar dengan sumbu Kepala Desa.
- [ ] Layout tidak berubah menjadi tree generik.

## 23.2 Garis

- [ ] Garis dibuat oleh AntV G6.
- [ ] Edge menggunakan `source` dan `target`.
- [ ] Edge menggunakan tipe `polyline`.
- [ ] Router menggunakan `orth`.
- [ ] Tidak ada garis putus-putus.
- [ ] Tidak ada celah antarsemen garis.
- [ ] Garis kiri membentuk satu bus vertikal.
- [ ] Garis kanan membentuk satu bus vertikal.
- [ ] Garis bawah membentuk satu bus horizontal.
- [ ] Garis utama turun dari Kepala Desa menuju bagian bawah.
- [ ] Edge tetap menempel saat pan.
- [ ] Edge tetap menempel saat zoom.
- [ ] Edge tetap benar setelah resize.

## 23.3 Node

- [ ] Foto berbentuk lingkaran.
- [ ] Jabatan terlihat pada kondisi awal.
- [ ] Nama tersembunyi pada kondisi awal.
- [ ] Nama muncul saat hover.
- [ ] Nama muncul saat fokus.
- [ ] Nama muncul saat ketukan.
- [ ] Node sedikit membesar ketika aktif.
- [ ] Hover tidak mengubah posisi graph.
- [ ] Tidak menggunakan kartu horizontal besar.
- [ ] Foto gagal dimuat menampilkan inisial.

## 23.4 Responsive

- [ ] Desktop menampilkan susunan penuh.
- [ ] Tablet mempertahankan susunan.
- [ ] Ponsel mempertahankan susunan.
- [ ] Tablet dan ponsel mendukung pan.
- [ ] Tablet dan ponsel mendukung zoom.
- [ ] Tidak ada scrollbar horizontal pada body halaman.
- [ ] Canvas menyesuaikan ukuran container.
- [ ] Graph kembali fit setelah resize.

---

# 24. Pengujian Manual

Uji minimal pada ukuran:

```text
1440 × 900
1280 × 800
1024 × 768
768 × 1024
430 × 932
390 × 844
360 × 800
```

Periksa:

1. Tidak ada node yang tertukar.
2. Tidak ada node yang keluar dari graph secara permanen.
3. Nama tidak terpotong ketika hover.
4. Garis tidak melewati foto secara tidak sengaja.
5. Garis tidak melintasi node lain.
6. Lima Kasun tetap satu baris.
7. Zoom tidak memisahkan edge dari node.
8. Pan tidak memisahkan edge dari node.
9. Resize tidak membuat garis tertinggal.
10. Navigasi AJAX tidak membuat canvas ganda.
11. Console tidak menghasilkan error.
12. `npm run build` berhasil.
13. Test Laravel berhasil.

---

# 25. Larangan Implementasi

Jangan melakukan hal berikut:

```text
× memakai LeaderLine;
× memakai garis CSS manual;
× memakai pseudo-element untuk cabang;
× memakai tabel HTML sebagai struktur;
× memakai tree card generik;
× membiarkan Dagre menyusun semua node;
× membuat lima Kasun menjadi dua baris;
× memindahkan tiga Kasi ke tengah;
× menempatkan Kaur sejajar horizontal;
× membuat nama selalu tampil;
× membuat node berbentuk kartu besar;
× memakai scrollbar horizontal body;
× membuat graph instance baru setiap resize;
× menyalin background atau banner poster referensi;
× mengubah arsitektur backend yang tidak terkait;
× mengubah data orang berdasarkan posisi visual.
```

---

# 26. Definition of Done untuk Codex

Setelah implementasi, Codex harus melaporkan:

1. Ringkasan pendekatan.
2. Daftar file yang diubah.
3. Dependensi yang ditambahkan.
4. Cara data Laravel dikonversi menjadi node dan edge.
5. Cara posisi layout dihitung.
6. Daftar virtual routing node.
7. Cara pan, zoom, fit view, dan resize bekerja.
8. Cara hover, fokus, dan ketukan bekerja.
9. Hasil `npm run build`.
10. Hasil test Laravel.
11. Kendala atau data foto yang belum tersedia.
12. Screenshot hasil desktop dan mobile bila lingkungan memungkinkan.

---

# 27. Prompt Ringkas yang Dapat Diberikan kepada Codex

```text
Baca seluruh dokumen ini sebelum mengubah kode.

Kerjakan pada branch "baru" di repo Website-Profil-Desa-Sukomulyo.

Implementasikan halaman /pemerintahan-desa menggunakan AntV G6 v5.
Pertahankan arsitektur Laravel dan data organisasi yang sudah ada.

Layout harus mengikuti gambar referensi secara khusus:
- Kepala Desa di tengah atas.
- Tiga Kasi vertikal di sisi kiri.
- Sekretaris Desa di kanan atas.
- Tiga Kaur vertikal di sisi kanan di bawah Sekdes.
- Lima Kasun satu baris horizontal di bagian bawah.
- Sumbu vertikal Kepala Desa turun ke bus horizontal lima Kasun.

Jangan memakai hasil layout Dagre generik.
Gunakan fixed reference coordinate layout berukuran 1440 × 760.
Gunakan layoutKey untuk menentukan posisi node.
Gunakan invisible routing nodes untuk membuat bus line kiri, kanan, dan bawah.
Gunakan edge polyline dengan router orth.
Node dan edge harus berada dalam satu graph AntV G6.
Gunakan autoResize, autoFit view, drag-canvas, dan zoom-canvas.

Node normal hanya menampilkan foto lingkaran dan jabatan.
Nama muncul saat hover, fokus keyboard, atau ketukan.
Hover hanya mentransform elemen HTML dalam node dan tidak mengubah posisi G6.

Pertahankan layout yang sama pada tablet dan ponsel melalui fit view, pan, dan zoom.
Jangan mengubahnya menjadi timeline.
Jangan membuat lima Kasun menjadi dua baris.
Jangan menggunakan scrollbar horizontal pada body.

Periksa implementasi lama pada government.blade.php, partial tree, app.css,
app.js, query/presenter organisasi, package.json, dan test.
Hapus renderer tree kartu lama dari halaman aktif tanpa merusak halaman lain.

Setelah selesai:
- jalankan npm run build;
- jalankan test Laravel;
- laporkan semua file yang berubah;
- jelaskan virtual nodes dan edge routing;
- laporkan apabila ada foto yang belum tersedia.
```

---

# 28. Referensi Teknis AntV G6

Gunakan dokumentasi resmi:

```text
https://g6.antv.antgroup.com/en/manual/graph/option
https://g6.antv.antgroup.com/en/manual/data
https://g6.antv.antgroup.com/en/manual/element/node/html
https://g6.antv.antgroup.com/en/manual/element/node/base-node
https://g6.antv.antgroup.com/en/manual/element/edge/polyline
https://g6.antv.antgroup.com/en/manual/element/edge/base-edge
https://g6.antv.antgroup.com/en/manual/behavior/drag-canvas
https://g6.antv.antgroup.com/en/manual/behavior/zoom-canvas
https://g6.antv.antgroup.com/en/api/render
https://g6.antv.antgroup.com/en/api/data
```

---

# 29. Kesimpulan Layout

Bentuk final wajib terbaca sebagai:

```text
            1 node di tengah atas

3 node vertikal kiri      1 Sekdes + 3 Kaur vertikal kanan

             5 node horizontal bawah
```

Bukan:

```text
semua anak Kepala Desa dalam satu baris
```

Bukan pula:

```text
tree rekursif simetris otomatis
```

AntV G6 dipakai untuk mempertahankan node, edge, pan, zoom, resize, dan routing dalam satu engine. Posisi khusus ditentukan oleh `layoutKey` dan koordinat referensi agar hasilnya konsisten dengan struktur Pemerintah Desa Sukomulyo.
