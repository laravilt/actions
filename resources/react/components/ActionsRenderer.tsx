import ActionButton from './ActionButton';

export interface ActionsRendererProps {
    actions?: any[];
    variant?: 'inline' | 'stack' | 'grid';
    gap?: 'sm' | 'default' | 'lg';
    align?: 'start' | 'center' | 'end';
}

export default function ActionsRenderer({ actions, variant = 'inline', gap = 'default', align = 'start' }: ActionsRendererProps) {
    if (!(actions && actions.length)) {
        return null;
    }

    const classes = ['flex'];

    // Variant
    if (variant === 'stack') {
        classes.push('flex-col');
    } else if (variant === 'grid') {
        classes.push('grid', 'grid-cols-2', 'md:grid-cols-3', 'lg:grid-cols-4');
    } else {
        classes.push('flex-row', 'flex-wrap');
    }

    // Gap
    const gapMap = {
        sm: 'gap-1',
        default: 'gap-2',
        lg: 'gap-4',
    };
    classes.push(gapMap[gap]);

    // Align
    if (variant !== 'grid') {
        const alignMap = {
            start: 'items-start',
            center: 'items-center',
            end: 'items-end',
        };
        classes.push(alignMap[align]);
    }

    const containerClass = classes.join(' ');

    return (
        <div className={containerClass}>
            {actions.map((action: any) => (
                <ActionButton key={action.name} {...action} />
            ))}
        </div>
    );
}
