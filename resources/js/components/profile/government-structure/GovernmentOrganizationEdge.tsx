import { BaseEdge, type Edge, type EdgeProps } from '@xyflow/react';

export type GovernmentOrganizationEdgeData = {
    route: 'direct' | 'left-main' | 'left-branch' | 'right-main' | 'right-branch' | 'bottom-main' | 'bottom-branch';
    trunkX?: number;
    trunkY?: number;
    trunkStartX?: number;
    trunkEndX?: number;
};

type GovernmentOrganizationEdgeModel = Edge<GovernmentOrganizationEdgeData, 'governmentOrganization'>;

const GovernmentOrganizationEdge = ({
    sourceX,
    sourceY,
    targetX,
    targetY,
    data,
}: EdgeProps<GovernmentOrganizationEdgeModel>) => {
    const edgeData = data ?? { route: 'direct' as const };
    const route = edgeData.route;
    let path: string;

    switch (route) {
        case 'left-main':
            path = `M ${sourceX} ${sourceY} H ${edgeData.trunkX} V ${targetY} H ${targetX}`;
            break;
        case 'left-branch':
            path = `M ${edgeData.trunkX} ${edgeData.trunkY} V ${targetY} H ${targetX}`;
            break;
        case 'right-main':
            path = `M ${sourceX} ${sourceY} H ${edgeData.trunkX} V ${targetY} H ${targetX}`;
            break;
        case 'right-branch':
            path = `M ${edgeData.trunkX} ${edgeData.trunkY} V ${targetY} H ${targetX}`;
            break;
        case 'bottom-main':
            path = `M ${sourceX} ${sourceY} V ${edgeData.trunkY} H ${edgeData.trunkEndX} M ${edgeData.trunkX} ${edgeData.trunkY} H ${edgeData.trunkStartX} M ${edgeData.trunkX} ${edgeData.trunkY} V ${targetY}`;
            break;
        case 'bottom-branch':
            path = `M ${targetX} ${edgeData.trunkY} V ${targetY}`;
            break;
        default:
            path = `M ${sourceX} ${sourceY} H ${(sourceX + targetX) / 2} V ${targetY} H ${targetX}`;
    }

    return (
        <BaseEdge
            path={path}
            interactionWidth={0}
            style={{
                stroke: 'var(--government-organization-edge)',
                strokeWidth: 1.7,
                strokeLinecap: 'square',
                strokeLinejoin: 'miter',
            }}
        />
    );
};

export default GovernmentOrganizationEdge;
