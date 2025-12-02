/**
 * A simple card component that can be used to display content in a card-like style.
 * @param {React.ReactNode} children - The children to render inside the card.
 * @param {string} className - The classes to apply to the card. Overwrites the default classes.
 * @param {number} transparency - The transparency of the card. Default is 40%.
 * @param {number} padding - The padding of the card. Default is 8.
 * @returns {React.ReactNode} - The card component.
 */
export default function Card({
    children,
    className = "",
    transparency = 40,
    padding = 8,
    ...props
}) {
    return (
        <div
            className={`p-${padding} rounded-2xl bg-gray-800/${transparency} backdrop-blur-sm border border-gray-700/50 shadow-2xl ${className}`}
            {...props}
        >
            {children}
        </div>
    );
}
