import React from "react";

const SwitchPermissions = ({ userId, isAdmin, isSuperAdmin, onToggle }) => {
    const handleToggle = () => {
        if (!isSuperAdmin) { // Seul un non-super admin peut déclencher onToggle
            onToggle(userId, isAdmin);
        }
    };

    return (
        <button
            className={`btn ${isSuperAdmin ? "btn-secondary" : isAdmin ? "btn-danger" : "btn-warning"}`}
            onClick={handleToggle}
            disabled={isSuperAdmin} // Le bouton est désactivé si c'est un super admin
        >
            {isSuperAdmin
                ? "Super Admin"
                : isAdmin
                ? "Retirer Admin"
                : "Donner Admin"}
        </button>
    );
};

export default SwitchPermissions;
