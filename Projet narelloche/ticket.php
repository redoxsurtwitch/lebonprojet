<?php
function getTicket($id) {
    $host = 'localhost'; 
    $dbname = 'phpticket_advanced'; 
    $username = 'admin'; 
    $password = 'admin'; 

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $query = "SELECT title, msg, created, ticket_status, id FROM tickets WHERE account_id = $id";
        $stmt = $pdo->query($query);

        if ($stmt->rowCount() > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Titre</th>
                        <th>Message</th>
                        <th>Date de Création</th>
                        <th>Statut</th>
                        <th>Réponse</th>
                    </tr>";

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                        <td>" . $row['title'] . "</td>
                        <td>" . $row['msg'] . "</td>
                        <td>" . $row['created'] . "</td>";
                        if ($row['ticket_status'] == "open") {
                            echo "<td><div class='status-open'>Ouvert</div></td>";
                        } elseif ($row['ticket_status'] == "resolved") {
                            echo "<td><div class='status-resolved'>Résolut</div></td>";
                        } elseif ($row['ticket_status'] == "closed") {
                            echo "<td><div class='status-closed'>Fermé</div></td>";
                        }
                        echo   "<td><a href='consulter.php?id=" . $row['id'] . "'class='btn-create-ticket'>Consulter</a></td>";
                echo "</tr>";
            }

            echo "</table>";
        } else {
            echo '<div class="ms-auto text-center pt-5"><p class="lead">Vous n\'avez pas encore de tickets..</p></div>
';
        }
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}

function getTicketAll($sort) {
    $host = 'localhost'; 
    $dbname = 'phpticket_advanced';
    $username = 'admin'; 
    $password = 'admin';
    $i = 0;
    
    if (isset($_GET['state'])) {$state = $_GET['state'];} else {header("Location: admin.php");}
    if (isset($_GET['prio'])) {$prio = $_GET['prio'];} else {header("Location: admin.php");}

    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $query = "SELECT id, title, msg, created, priority, ticket_status FROM tickets";
        
        if ($sort == "recent") {$query = "SELECT id, title, msg, created, priority, ticket_status FROM tickets ORDER BY created DESC";}
        if ($sort == "old") {$query = "SELECT id, title, msg, created, priority, ticket_status FROM tickets ORDER BY created ASC";}
        if ($sort == "state") {$query = "SELECT id, title, msg, created, priority, ticket_status FROM tickets WHERE ticket_status = $state";}
        if ($sort == "prio") {$query = "SELECT id, title, msg, created, priority, ticket_status FROM tickets WHERE priority = $prio";}

        $stmt = $pdo->query($query);

        if ($stmt->rowCount() > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Titre</th>
                        <th>Message</th>
                        <th>Date de Création</th>
                        <th>Priorité</th>
                        <th>Statut</th>
                        <th>Actions</th>
                        <th>Réponse</th>
                    </tr>";

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<tr>
                        <td>" . $row['title'] . "</td>
                        <td>" . $row['msg'] . "</td>
                        <td>" . $row['created'] . "</td>";
                        if ($row['priority'] == "low") {
                            echo "<td><div class='prio-low'>Faible</div></td>";
                        } elseif ($row['priority'] == "medium") {
                            echo "<td><div class='prio-med'>Moyenne</div></td>";
                        } elseif ($row['priority'] == "high") {
                            echo "<td><div class='prio-high'>Elévée</div></td>";
                        }

                        if ($row['ticket_status'] == "open") {
                            echo "<td><div class='status-open'>Ouvert</div></td>";
                        } elseif ($row['ticket_status'] == "resolved") {
                            echo "<td><div class='status-resolved'>Résolut</div></td>";
                        } elseif ($row['ticket_status'] == "closed") {
                            echo "<td><div class='status-closed'>Fermé</div></td>";
                        }
                echo   "<td><a href='gerer-ticket.php?id=" . $row['id'] . "'class='btn-create-ticket'>Gérer</a></td>
                        <td><a href='repondre.php?id=" . $row['id'] . "'class='btn-create-ticket'>Répondre</a></td>
                    </tr>";
            }

            echo "</table>";
        } else {
            echo "Aucun ticket trouvé.";
        }
    } catch (PDOException $e) {
        echo "Erreur de connexion à la base de données : " . $e->getMessage();
    }
}


function creerTicket() {

    $title = $_POST['title'];
    $message = $_POST['message'];
    $category_id = $_POST['category'];

    $full_name = $_SESSION['username'];
    $email = $_SESSION['email'];
    $account_id = $_SESSION['user_id'];

    $host = 'localhost';
    $db_username = 'admin';
    $db_password = 'admin';
    $dbname = 'phpticket_advanced';

    $conn = new mysqli($host, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    $result = $conn->query("SELECT MAX(id) AS max_id FROM tickets");
    if ($result) {
        $row = $result->fetch_assoc();
        $last_id = $row['max_id'] ?? 0;
    } else {
        die("Erreur lors de la récupération du dernier ID : " . $conn->error);
    }

    $new_id = $last_id + 1;

    $sql = "INSERT INTO tickets (id, title, msg, full_name, email, created, ticket_status, priority, category_id, private, account_id, approved) 
            VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, 'open', 'low', ?, 0, ?, 1)";

    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("issssii", $new_id, $title, $message, $full_name, $email, $category_id, $account_id);
        $stmt->execute();

        $sql = "INSERT INTO tickets_uploads (ticket_id, filepath) VALUES (?, ?)";
        $stmt->bind_param("is", $new_id, $file);
        $stmt->execute();

        header("Location: ../mail.php?action=1&user=" .$full_name. ""); 
        exit();

        $stmt->close();
    } else {
        echo "Erreur de préparation de la requête : " . $conn->error;
    }

    $conn->close();
}

function gererTicket() {

        $ticket_status = $_POST['ticket_status'];
        $priority = $_POST['priority'];
        $ticket_id = intval($_GET['id']);

        $host = 'localhost';
        $db_username = 'admin';
        $db_password = 'admin';
        $dbname = 'phpticket_advanced';

        $conn = new mysqli($host, $db_username, $db_password, $dbname);

        if ($conn->connect_error) {
            die("Échec de la connexion : " . $conn->connect_error);
        }

        $sql = "UPDATE tickets SET ticket_status = ?, priority = ? WHERE id = ?";

        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ssi", $ticket_status, $priority, $ticket_id);

            if ($stmt->execute()) {
                header("Location: gerer-ticket.php?id=" . $ticket_id . "&success=true"); 
                exit();
            } else {
                echo "Erreur lors de la mise à jour du ticket : " . $stmt->error;
            }

            $stmt->close();
        } else {
            echo "Erreur de préparation de la requête : " . $conn->error;
        }
        $conn->close();
}

function getModif() {
    $host = 'localhost';
    $db_username = 'admin';
    $db_password = 'admin';
    $dbname = 'phpticket_advanced';

    $conn = new mysqli($host, $db_username, $db_password, $dbname);

    if ($conn->connect_error) {
        die("Échec de la connexion : " . $conn->connect_error);
    }

    if (isset($_GET['id'])) {
        $ticket_id = intval($_GET['id']);

        $sql = "SELECT id, title, msg, created, ticket_status, priority FROM tickets WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("i", $ticket_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $ticket = $result->fetch_assoc();
            } else {
                die("Ticket introuvable.");
            }

            $stmt->close();
        } else {
            die("Erreur de préparation de la requête : " . $conn->error);
        }
    } else {
        die("ID du ticket manquant.");
    }

    $conn->close();
    }

function reponseTicket() {
    $message = $_POST['message'];
        $account_id = $_SESSION['user_id'];
        $ticket_id = intval($_GET['id']);
        $host = 'localhost';
        $db_username = 'admin';
        $db_password = 'admin';
        $dbname = 'phpticket_advanced';
        $conn = new mysqli($host, $db_username, $db_password, $dbname);
        if ($conn->connect_error) {
            die("Échec de la connexion : " . $conn->connect_error);
        }
        $sql = "INSERT INTO tickets_comments (ticket_id, msg, account_id) VALUES (?, ?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("isi", $ticket_id, $message, $account_id);
            if ($stmt->execute()) {
                header("Location: repondre.php?id=" . $ticket_id . "&success=true");
                exit();
            } else {
                echo "Erreur lors de la mise à jour du ticket : " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Erreur de préparation de la requête : " . $conn->error;
        }
        $conn->close();
}
?>