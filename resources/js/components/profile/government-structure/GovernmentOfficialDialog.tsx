import { useEffect, useRef, type SyntheticEvent } from 'react';
import {
    officialGroupLabels,
    resolveOfficialPhoto,
} from '../../../data/governmentOfficials';
import type { GovernmentOfficial } from './types';

interface GovernmentOfficialDialogProps {
    official: GovernmentOfficial;
    onDismiss: () => void;
}

const GovernmentOfficialDialog = ({
    official,
    onDismiss,
}: GovernmentOfficialDialogProps) => {
    const dialogRef = useRef<HTMLDialogElement>(null);
    const openerRef = useRef<HTMLElement | null>(null);

    useEffect(() => {
        openerRef.current = document.activeElement as HTMLElement | null;
        const dialog = dialogRef.current;

        if (dialog && !dialog.open) dialog.showModal();

        return () => {
            if (dialog?.open) dialog.close();
            openerRef.current?.focus?.();
        };
    }, []);

    const handleImageError = (event: SyntheticEvent<HTMLImageElement>) => {
        const image = event.currentTarget;
        if (image.src.endsWith(official.fallbackImage)) return;
        image.src = official.fallbackImage;
        image.alt = `Avatar ${official.name}`;
    };

    return (
        <dialog
            ref={dialogRef}
            className="government-official-dialog"
            aria-labelledby="government-official-dialog-title"
            onCancel={(event) => {
                event.preventDefault();
                onDismiss();
            }}
            onClick={(event) => {
                if (event.target === event.currentTarget) onDismiss();
            }}
        >
            <button
                type="button"
                className="government-official-dialog__close"
                aria-label="Tutup detail perangkat desa"
                onClick={onDismiss}
            >
                <i className="fas fa-times" aria-hidden="true" />
            </button>
            <div className="government-official-dialog__portrait">
                <i className="fas fa-user" aria-hidden="true" />
                <img
                    src={resolveOfficialPhoto(official)}
                    alt={`${official.image ? 'Foto' : 'Avatar'} ${official.name}`}
                    onError={handleImageError}
                />
            </div>
            <div className="government-official-dialog__content">
                <span>{officialGroupLabels[official.group]}</span>
                <h2 id="government-official-dialog-title">{official.name}</h2>
                <p>{official.position}</p>
            </div>
        </dialog>
    );
};

export default GovernmentOfficialDialog;
