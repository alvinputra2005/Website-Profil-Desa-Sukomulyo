import type {
    GovernmentOfficial,
    OfficialGroup,
} from '../components/profile/government-structure/types';

export const DEFAULT_OFFICIAL_AVATAR = '/assets/male-resident-avatar.jpg';

const official = (
    data: Omit<GovernmentOfficial, 'fallbackImage'>,
): GovernmentOfficial => ({
    ...data,
    fallbackImage: DEFAULT_OFFICIAL_AVATAR,
});

export const governmentOfficials: GovernmentOfficial[] = [
    official({
        id: 'kepala-desa',
        name: 'Safiul Anwar, ST',
        position: 'Kepala Desa',
        image: '/assets/safiul-anwar.jpeg',
        group: 'leadership',
        positionDesktop: { x: 630, y: 20 },
    }),
    official({
        id: 'sekretaris-desa',
        name: 'Baktiyar Kufain',
        position: 'Sekretaris Desa',
        group: 'secretariat',
        parentId: 'kepala-desa',
        positionDesktop: { x: 1110, y: 145 },
    }),
    official({
        id: 'kasi-pemerintahan',
        name: 'Angga Saputra',
        position: 'Kasi Pemerintahan',
        image: '/assets/angga-saputra.jpeg',
        group: 'section',
        parentId: 'kepala-desa',
        positionDesktop: { x: 65, y: 190 },
    }),
    official({
        id: 'kasi-pelayanan',
        name: 'Wike Priharti Y',
        position: 'Kasi Pelayanan',
        image: '/assets/wike-priharti-y.jpeg',
        group: 'section',
        parentId: 'kepala-desa',
        positionDesktop: { x: 65, y: 315 },
    }),
    official({
        id: 'kasi-kesejahteraan',
        name: 'Mohamad Sholeh',
        position: 'Kasi Kesejahteraan',
        image: '/assets/muhammad-sholeh.jpeg',
        group: 'section',
        parentId: 'kepala-desa',
        positionDesktop: { x: 65, y: 440 },
    }),
    official({
        id: 'kaur-keuangan',
        name: 'Suwarno',
        position: 'Kaur Keuangan',
        image: '/assets/suwarno.jpeg',
        group: 'administration',
        parentId: 'sekretaris-desa',
        positionDesktop: { x: 1190, y: 300 },
    }),
    official({
        id: 'kaur-perencanaan',
        name: 'Reza Tri Purnomo',
        position: 'Kaur Perencanaan',
        image: '/assets/reza-tri.jpeg',
        group: 'administration',
        parentId: 'sekretaris-desa',
        positionDesktop: { x: 1190, y: 415 },
    }),
    official({
        id: 'kaur-tu-umum',
        name: 'Catur Yulianto',
        position: 'Kaur Tata Usaha & Umum',
        image: '/assets/catur-yulianto.jpeg',
        group: 'administration',
        parentId: 'sekretaris-desa',
        positionDesktop: { x: 1190, y: 530 },
    }),
    official({
        id: 'kasun-bakir',
        name: 'Bambang S',
        position: 'Kasun Bakir',
        image: '/assets/bambang.jpeg',
        group: 'hamlet',
        parentId: 'kepala-desa',
        positionDesktop: { x: 115, y: 665 },
    }),
    official({
        id: 'kasun-biyan',
        name: 'Sispanaji',
        position: 'Kasun Biyan',
        image: '/assets/sispanaji.jpeg',
        group: 'hamlet',
        parentId: 'kepala-desa',
        positionDesktop: { x: 365, y: 665 },
    }),
    official({
        id: 'kasun-gumuk',
        name: 'Nikita F Z',
        position: 'Kasun Gumuk',
        image: '/assets/nikita.jpeg',
        group: 'hamlet',
        parentId: 'kepala-desa',
        positionDesktop: { x: 615, y: 665 },
    }),
    official({
        id: 'kasun-kedungrejo',
        name: 'Fendi Priyo S',
        position: 'Kasun Kedungrejo',
        image: '/assets/fendi-priyo.jpeg',
        group: 'hamlet',
        parentId: 'kepala-desa',
        positionDesktop: { x: 865, y: 665 },
    }),
    official({
        id: 'kasun-talasan',
        name: 'Cahyo Utomo',
        position: 'Kasun Talasan',
        group: 'hamlet',
        parentId: 'kepala-desa',
        positionDesktop: { x: 1115, y: 665 },
    }),
];

export const officialGroupLabels: Record<OfficialGroup, string> = {
    leadership: 'Pimpinan Desa',
    secretariat: 'Sekretariat Desa',
    section: 'Pelaksana Teknis',
    administration: 'Urusan Sekretariat',
    hamlet: 'Kepala Dusun',
};

export const resolveOfficialPhoto = (officialData: GovernmentOfficial): string => (
    officialData.image || officialData.fallbackImage
);

