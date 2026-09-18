<?php
session_start();

/* Admin login check */
if (
    !isset($_SESSION['admin_logged_in']) ||
    $_SESSION['admin_logged_in'] !== true
) {
    header("Location: login.php");
    exit();
}

require_once "../config/db.php";


/* Get ALL projects from ALL existing users */
$sql = "
    SELECT
        p.id,
        p.title,
        p.description,
        p.technologies,
        p.demo_link,
        p.user_id,
        u.fullname,
        u.username,
        u.email
    FROM projects p
    INNER JOIN users u
        ON p.user_id = u.id
    ORDER BY p.id DESC
";

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Admin - Projects</title>

    <!-- EXISTING CSS -->
    <link rel="stylesheet" href="style.css">

    <!-- Font Awesome -->
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

</head>

<body>

<?php include "sidebar.php"; ?>


<div class="content">

    <div class="card">

        <h2>
            <i class="fa-solid fa-folder-open"></i>
            Projects
        </h2>

        <p>
            All projects added by registered users
        </p>


        <?php if ($result && $result->num_rows > 0): ?>

            <div style="overflow-x:auto;">

                <table style="
                    width:100%;
                    border-collapse:collapse;
                    margin-top:20px;
                ">

                    <thead>

                        <tr style="
                            background:#0b1d51;
                            color:white;
                        ">

                            <th style="padding:12px;">#</th>

                            <th style="padding:12px;">
                                User
                            </th>

                            <th style="padding:12px;">
                                Project Title
                            </th>

                            <th style="padding:12px;">
                                Description
                            </th>

                            <th style="padding:12px;">
                                Technologies
                            </th>

                            <th style="padding:12px;">
                                Demo
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php
                    $count = 1;

                    while ($row = $result->fetch_assoc()):
                    ?>

                        <tr style="
                            border-bottom:1px solid #ddd;
                        ">

                            <td style="padding:12px;">
                                <?php echo $count++; ?>
                            </td>


                            <td style="padding:12px;">

                                <strong>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['fullname']
                                    );
                                    ?>
                                </strong>

                                <br>

                                <small>
                                    @<?php
                                    echo htmlspecialchars(
                                        $row['username']
                                    );
                                    ?>
                                </small>

                                <br>

                                <small>
                                    <?php
                                    echo htmlspecialchars(
                                        $row['email']
                                    );
                                    ?>
                                </small>

                            </td>


                            <td style="padding:12px;">

                                <?php
                                echo htmlspecialchars(
                                    $row['title']
                                );
                                ?>

                            </td>


                            <td style="padding:12px;">

                                <?php
                                echo htmlspecialchars(
                                    $row['description']
                                );
                                ?>

                            </td>


                            <td style="padding:12px;">

                                <?php
                                echo htmlspecialchars(
                                    $row['technologies']
                                );
                                ?>

                            </td>


                            <td style="padding:12px;">

                                <?php if (!empty($row['demo_link'])): ?>

                                    <a
                                        href="<?php echo htmlspecialchars($row['demo_link']); ?>"
                                        target="_blank"
                                    >
                                        View
                                    </a>

                                <?php else: ?>

                                    No Link

                                <?php endif; ?>

                            </td>

                        </tr>

                    <?php endwhile; ?>

                    </tbody>

                </table>

            </div>

        <?php else: ?>

            <p style="margin-top:20px;">
                No projects found.
            </p>

        <?php endif; ?>

    </div>

</div>

</body>
</html>