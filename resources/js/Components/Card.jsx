export default function Card({ children, className = "", transparency = 40, ...props }) {
    return (
        <div
            className={`p-8 rounded-2xl bg-gray-800/${transparency} backdrop-blur-sm border border-gray-700/50 shadow-2xl ${className}`}
            {...props}
        >
            {children}
        </div>
    );
}
