import { memo, type SyntheticEvent } from 'react';
import {
    Handle,
    Position,
    type NodeProps,
} from '@xyflow/react';
import type { GovernmentOfficialNodeType } from './types';

const hiddenHandleStyle = {
    width: 1,
    height: 1,
    border: 0,
    opacity: 0,
    pointerEvents: 'none' as const,
};

const GovernmentOfficialNode = ({ data, selected }: NodeProps<GovernmentOfficialNodeType>) => {
    const { official } = data;

    const handleImageError = (event: SyntheticEvent<HTMLImageElement>) => {
        const image = event.currentTarget;

        if (image.src.endsWith(official.fallbackImage)) {
            image.hidden = true;
            return;
        }

        image.src = official.fallbackImage;
        image.alt = `Avatar ${official.name}`;
    };

    return (
        <div className={`government-official-node government-official-node--${official.group}${selected ? ' is-selected' : ''}`}>
            <Handle id="target-top" type="target" position={Position.Top} style={hiddenHandleStyle} />
            <Handle id="target-left" type="target" position={Position.Left} style={hiddenHandleStyle} />
            <Handle id="target-right" type="target" position={Position.Right} style={hiddenHandleStyle} />

            <button
                type="button"
                className="government-official-node__button nodrag nopan"
                aria-label={`Lihat detail ${official.position} ${official.name}`}
                title={`${official.position} — ${official.name}`}
                onClick={() => data.onOpen(official)}
            >
                <span className="government-official-node__portrait">
                    <i className="fas fa-user" aria-hidden="true" />
                    <img
                        src={data.image}
                        alt={`${data.usesFallback ? 'Avatar' : 'Foto'} ${official.name}`}
                        loading={official.id === 'kepala-desa' ? 'eager' : 'lazy'}
                        decoding="async"
                        onError={handleImageError}
                    />
                </span>
                <span className="government-official-node__copy">
                    <small>{official.position}</small>
                    <strong>{official.name}</strong>
                </span>
            </button>

            <Handle id="source-bottom" type="source" position={Position.Bottom} style={hiddenHandleStyle} />
            <Handle id="source-left" type="source" position={Position.Left} style={hiddenHandleStyle} />
            <Handle id="source-right" type="source" position={Position.Right} style={hiddenHandleStyle} />
        </div>
    );
};

export default memo(GovernmentOfficialNode);
