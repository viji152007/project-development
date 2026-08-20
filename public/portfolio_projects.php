<?php
include("../../config/db.php");

$result = $conn->query(
    "SELECT * FROM projects ORDER BY id DESC"
);
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Projects</title>

<link rel="stylesheet" href="../../css/style.css">

</head>

<body>

    <!-- ==============================
         PROJECTS SECTION
    =============================== -->

    <section class="projects-section">

        <div class="container">

            <div class="section-header">

                <h1>My Projects</h1>

                <p>
                    Explore my projects and technical work.
                </p>

            </div>


            <?php if ($result && $result->num_rows > 0): ?>

                <div class="projects-grid">

                    <?php while ($project = $result->fetch_assoc()): ?>

                        <div class="card project-card">


                            <!-- PROJECT IMAGE -->

                            <?php if (!empty($project['image'])): ?>

                                <div class="project-image">

                                    <img
                                        src="../../uploads/projects/<?php echo htmlspecialchars($project['image']); ?>"
                                        alt="<?php echo htmlspecialchars($project['title']); ?>"
                                    >

                                </div>

                            <?php endif; ?>


                            <!-- PROJECT DETAILS -->

                            <div class="card-body">

                                <h2>
                                    <?php
                                    echo htmlspecialchars(
                                        $project['title']
                                    );
                                    ?>
                                </h2>


                                <p>
                                    <?php
                                    echo nl2br(
                                        htmlspecialchars(
                                            $project['description']
                                        )
                                    );
                                    ?>
                                </p>


                                <!-- TECHNOLOGIES -->

                                <?php if (!empty($project['technologies'])): ?>

                                    <p>

                                        <strong>
                                            Technologies:
                                        </strong>

                                        <?php
                                        echo htmlspecialchars(
                                            $project['technologies']
                                        );
                                        ?>

                                    </p>

                                <?php endif; ?>


                                <!-- DEMO -->

                                <?php if (!empty($project['demo_link'])): ?>

                                    <a
                                        href="<?php echo htmlspecialchars($project['demo_link']); ?>"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="btn-primary"
                                    >
                                        View Demo
                                    </a>

                                <?php endif; ?>

                            </div>

                        </div>

                    <?php endwhile; ?>

                </div>

            <?php else: ?>

                <div class="card">

                    <div class="card-body">

                        <h3>No Projects Available</h3>

                        <p>
                            Projects will appear here once they are added.
                        </p>

                    </div>

                </div>

            <?php endif; ?>

        </div>

    </section>

</body>

</html>