import type { Node } from '@xyflow/react';

export type OfficialGroup =
    | 'leadership'
    | 'secretariat'
    | 'section'
    | 'administration'
    | 'hamlet';

export interface ChartPosition {
    x: number;
    y: number;
}

export interface GovernmentOfficial {
    id: string;
    name: string;
    position: string;
    image?: string;
    fallbackImage: string;
    group: OfficialGroup;
    parentId?: string;
    positionDesktop: ChartPosition;
    positionCompact?: ChartPosition;
}

export interface GovernmentOfficialNodeData extends Record<string, unknown> {
    official: GovernmentOfficial;
    image: string;
    usesFallback: boolean;
    onOpen: (official: GovernmentOfficial) => void;
}

export type GovernmentOfficialNodeType = Node<
    GovernmentOfficialNodeData,
    'governmentOfficial'
>;

