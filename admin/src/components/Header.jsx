import React, { useState, useEffect } from "react";
import { Link } from "react-router-dom";
import { Navbar, Nav, Container } from "react-bootstrap";

const Header = () => {
    const [isAuthenticated, setIsAuthenticated] = useState(!!localStorage.getItem("token"));
    const [isAdmin, setIsAdmin] = useState(false);

    useEffect(() => {
        // Fonction pour vérifier l'authentification et le rôle
        const checkAuth = () => {
            const token = localStorage.getItem("token");
            const user = token ? JSON.parse(localStorage.getItem("user")) : null;
            setIsAuthenticated(!!token);
            setIsAdmin(user && user.is_admin === 1);
        };

        // Exécuter la vérification initiale
        checkAuth();

        // Mettre à jour l'authentification si le stockage change
        window.addEventListener("storage", checkAuth);

        // Mettre à jour lorsque `authChange` est déclenché dans le même onglet
        window.addEventListener("authChange", checkAuth);


        // Nettoyage
        return () => {
            window.removeEventListener("storage", checkAuth);
            window.removeEventListener("authChange", checkAuth);
        };
    }, []);

    return (
        <Navbar bg="dark" variant="dark">
            <Container>
                <Nav className="me-auto">
                    <Nav.Link as={Link} to="/">Home</Nav.Link>
                    <Nav.Link as={Link} to="/connection">Connection</Nav.Link>
                    {isAuthenticated && isAdmin && (
                        <Nav.Link as={Link} to="/inscription">Inscription</Nav.Link>
                    )}
                    <Nav.Link as={Link} to="/tasks">Tâches</Nav.Link>
                </Nav>
            </Container>
        </Navbar>
    );
};

export default Header;
