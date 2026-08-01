import { createRoot } from 'react-dom/client';
import '@xyflow/react/dist/style.css';
import GovernmentOrganizationChart from './components/profile/government-structure/GovernmentOrganizationChart';

export const initGovernmentOrganization = () => {
    const container = document.querySelector<HTMLElement>('[data-government-organization-root]');

    if (!container || container.dataset.governmentOrganizationBound === 'true') return;

    container.dataset.governmentOrganizationBound = 'true';
    const root = createRoot(container);
    root.render(<GovernmentOrganizationChart />);

    window.addEventListener('ajax:before-render', () => root.unmount(), { once: true });
};

