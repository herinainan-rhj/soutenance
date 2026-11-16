import { Link } from "react-router-dom";

export default function Logo({ to = "/", light = false }) {
    return (
        <Link
            to={to}
            className={`logo ${light ? "logo-light" : ""}`}
            aria-label="E RAY — accueil"
        >
            <img src="/eray.png" alt="Logo Eray" className="logo-circle" />
            <span className="logo-text">ERAY</span>
        </Link>
    );
}