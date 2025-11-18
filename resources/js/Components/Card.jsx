export default function Card({ children, className = "", ...props }) {
    return (
        <div
            className={`p-8 rounded-2xl bg-gray-800/40 backdrop-blur-sm border border-gray-700/50 shadow-2xl ${className}`}
            {...props}
        >
            {children}
        </div>
    );
}
