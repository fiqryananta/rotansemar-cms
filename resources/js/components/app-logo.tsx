export default function AppLogo() {
    return (
        <div className="flex items-center justify-center overflow-hidden">
            <img
                src="/images/logo-rotansemar.png"
                alt="Logo Rotan Semar"
                className="object-contain group-data-[collapsible=icon]:hidden"
            />
            <img
                src="/images/logo-rotansemar-crop.png"
                alt="Logo Rotan Semar"
                className="hidden h-8 w-8 object-contain group-data-[collapsible=icon]:block"
            />
        </div>
    );
}
