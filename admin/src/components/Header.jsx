import React from "react";
import { Link } from "react-router-dom";
import { Navbar, Nav, Container } from "react-bootstrap";

const Header = () => {
    return (
        <Navbar bg="dark" variant="dark">
            <Container>
                <Nav className="me-auto">
                    <Nav.Link as={Link} to="/">Home</Nav.Link>
                    <Nav.Link as={Link} to="/connection">Connection</Nav.Link>
                    <Nav.Link as={Link} to="/inscription">Inscription</Nav.Link>
                    <Nav.Link as={Link} to="/tasks">Tâches</Nav.Link>
                </Nav>
            </Container>
        </Navbar>
    );
}

export default Header;
