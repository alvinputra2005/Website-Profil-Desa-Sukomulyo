import {
    useCallback,
    useEffect,
    useMemo,
    useState,
} from 'react';
import {
    ReactFlow,
    ReactFlowProvider,
    type Edge,
    type ReactFlowInstance,
} from '@xyflow/react';
import {
    governmentOfficials,
    officialGroupLabels,
    resolveOfficialPhoto,
} from '../../../data/governmentOfficials';
import GovernmentOfficialDialog from './GovernmentOfficialDialog';
import GovernmentOfficialNode from './GovernmentOfficialNode';
import GovernmentOrganizationEdge, { type GovernmentOrganizationEdgeData } from './GovernmentOrganizationEdge';
import type {
    GovernmentOfficial,
    GovernmentOfficialNodeType,
    OfficialGroup,
} from './types';

const nodeTypes = {
    governmentOfficial: GovernmentOfficialNode,
};

const edge = (data: GovernmentOrganizationEdgeData, values: Partial<Edge> = {}): Edge => ({
    id: values.id ?? `${values.source}-${values.target}`,
    source: values.source ?? 'kepala-desa',
    target: values.target ?? 'kepala-desa',
    sourceHandle: values.sourceHandle,
    targetHandle: values.targetHandle,
    type: 'governmentOrganization',
    data,
    animated: false,
    selectable: false,
    deletable: false,
    focusable: false,
});

const organizationEdges: Edge[] = [
    edge({ route: 'left-main', trunkX: 315 }, {
        id: 'kepala-desa-kasi-pemerintahan', source: 'kepala-desa', sourceHandle: 'source-left',
        target: 'kasi-pemerintahan', targetHandle: 'target-right',
    }),
    edge({ route: 'left-branch', trunkX: 315, trunkY: 233 }, {
        id: 'left-trunk-kasi-pelayanan', source: 'kepala-desa', sourceHandle: 'source-left',
        target: 'kasi-pelayanan', targetHandle: 'target-right',
    }),
    edge({ route: 'left-branch', trunkX: 315, trunkY: 233 }, {
        id: 'left-trunk-kasi-kesejahteraan', source: 'kepala-desa', sourceHandle: 'source-left',
        target: 'kasi-kesejahteraan', targetHandle: 'target-right',
    }),
    edge({ route: 'direct' }, {
        id: 'kepala-desa-sekretaris-desa', source: 'kepala-desa', sourceHandle: 'source-right',
        target: 'sekretaris-desa', targetHandle: 'target-left',
    }),
    edge({ route: 'right-main', trunkX: 1160 }, {
        id: 'sekretaris-desa-kaur-keuangan', source: 'sekretaris-desa', sourceHandle: 'source-bottom-kaur',
        target: 'kaur-keuangan', targetHandle: 'target-left',
    }),
    edge({ route: 'right-branch', trunkX: 1160, trunkY: 283 }, {
        id: 'right-trunk-kaur-perencanaan', source: 'sekretaris-desa', sourceHandle: 'source-bottom',
        target: 'kaur-perencanaan', targetHandle: 'target-left',
    }),
    edge({ route: 'right-branch', trunkX: 1160, trunkY: 283 }, {
        id: 'right-trunk-kaur-tu-umum', source: 'sekretaris-desa', sourceHandle: 'source-bottom',
        target: 'kaur-tu-umum', targetHandle: 'target-left',
    }),
    edge({ route: 'bottom-main', trunkX: 740, trunkY: 610, trunkStartX: 225, trunkEndX: 1225 }, {
        id: 'kepala-desa-kasun-gumul', source: 'kepala-desa', sourceHandle: 'source-bottom',
        target: 'kasun-gumul', targetHandle: 'target-top',
    }),
    ...([
        ['kasun-bakir', 225], ['kasun-biyan', 475], ['kasun-kedungrejo', 975], ['kasun-talasan', 1225],
    ] as Array<[string, number]>).map(([target, targetX]) => edge({ route: 'bottom-branch', trunkX: targetX, trunkY: 610 }, {
        id: `bottom-trunk-${target}`, source: 'kepala-desa', sourceHandle: 'source-bottom',
        target: target as string, targetHandle: 'target-top',
    })),
];

const edgeTypes = {
    governmentOrganization: GovernmentOrganizationEdge,
};

const groups: OfficialGroup[] = [
    'leadership',
    'secretariat',
    'section',
    'administration',
    'hamlet',
];

const useCompactViewport = () => {
    const [compact, setCompact] = useState(() => window.matchMedia('(max-width: 767px)').matches);

    useEffect(() => {
        const media = window.matchMedia('(max-width: 767px)');
        const update = () => setCompact(media.matches);
        media.addEventListener('change', update);
        return () => media.removeEventListener('change', update);
    }, []);

    return compact;
};

const GovernmentOrganizationFlow = () => {
    const compact = useCompactViewport();
    const [selectedOfficial, setSelectedOfficial] = useState<GovernmentOfficial | null>(null);
    const [listVisible, setListVisible] = useState(false);

    const openOfficial = useCallback((official: GovernmentOfficial) => {
        setSelectedOfficial(official);
    }, []);

    const nodes = useMemo<GovernmentOfficialNodeType[]>(() => governmentOfficials.map((official) => ({
        id: official.id,
        type: 'governmentOfficial',
        position: compact && official.positionCompact
            ? official.positionCompact
            : official.positionDesktop,
        draggable: false,
        selectable: true,
        selected: selectedOfficial?.id === official.id,
        data: {
            official,
            image: resolveOfficialPhoto(official),
            usesFallback: !official.image,
            onOpen: openOfficial,
        },
    })), [compact, openOfficial, selectedOfficial]);

    const initializeViewport = useCallback((instance: ReactFlowInstance<GovernmentOfficialNodeType, Edge>) => {
        window.requestAnimationFrame(() => {
            if (compact) {
                void instance.setCenter(740, 100, { zoom: 0.78, duration: 0 });
                return;
            }

            void instance.fitView({
                padding: 0.02,
                minZoom: 0.45,
                maxZoom: 1,
                duration: 0,
            });
        });
    }, [compact]);

    useEffect(() => {
        if (!compact) setListVisible(false);
    }, [compact]);

    return (
        <>
            <div
                className="government-organization-chart"
                role="region"
                aria-label="Diagram interaktif struktur Pemerintah Desa Sukomulyo"
            >
                <ReactFlow
                    nodes={nodes}
                    edges={organizationEdges}
                    nodeTypes={nodeTypes}
                    edgeTypes={edgeTypes}
                    onInit={initializeViewport}
                    minZoom={0.35}
                    maxZoom={1.5}
                    nodesDraggable={false}
                    nodesConnectable={false}
                    elementsSelectable
                    nodesFocusable
                    edgesFocusable={false}
                    edgesReconnectable={false}
                    deleteKeyCode={null}
                    selectionKeyCode={null}
                    multiSelectionKeyCode={null}
                    panOnDrag={false}
                    panOnScroll={false}
                    zoomOnPinch={false}
                    zoomOnScroll={false}
                    zoomOnDoubleClick={false}
                    preventScrolling={false}
                    proOptions={{ hideAttribution: true }}
                    defaultEdgeOptions={{
                        type: 'step',
                        selectable: false,
                        deletable: false,
                        focusable: false,
                    }}
                />
            </div>

            {compact && (
                <button
                    type="button"
                    className="government-organization-list-toggle"
                    aria-expanded={listVisible}
                    aria-controls="government-organization-list"
                    onClick={() => setListVisible((visible) => !visible)}
                >
                    <i className="fas fa-list" aria-hidden="true" />
                    {listVisible ? 'Sembunyikan daftar' : 'Lihat sebagai daftar'}
                </button>
            )}

            {listVisible && (
                <div id="government-organization-list" className="government-organization-list">
                    {groups.map((group) => {
                        const officials = governmentOfficials.filter((official) => official.group === group);

                        return (
                            <details key={group} open={group === 'leadership'}>
                                <summary>{officialGroupLabels[group]}</summary>
                                <ul>
                                    {officials.map((official) => (
                                        <li key={official.id}>
                                            <button type="button" onClick={() => openOfficial(official)}>
                                                <span>{official.position}</span>
                                                <strong>{official.name}</strong>
                                            </button>
                                        </li>
                                    ))}
                                </ul>
                            </details>
                        );
                    })}
                </div>
            )}

            {selectedOfficial && (
                <GovernmentOfficialDialog
                    key={selectedOfficial.id}
                    official={selectedOfficial}
                    onDismiss={() => setSelectedOfficial(null)}
                />
            )}
        </>
    );
};

const GovernmentOrganizationChart = () => (
    <ReactFlowProvider>
        <GovernmentOrganizationFlow />
    </ReactFlowProvider>
);

export default GovernmentOrganizationChart;
