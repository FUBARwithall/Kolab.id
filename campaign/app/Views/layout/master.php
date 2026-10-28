<!-- Header -->
<?= $this->include('layout/header') ?>
<!-- /Header -->

<body class="layout-fixed light-mode">
    <div class="wrapper">

        <!-- Navbar -->
        <?= $this->include('layout/navbar') ?>
        <!-- /.navbar -->

        <!-- Main Sidebar Container -->
        <?= $this->include('layout/sidebar') ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <div class="content-header">
                <div class="container-fluid">
                    <div class="row mb-2 align-items-center">
                        <div class="col-sm-6">
                            <h1 class="m-0 fs-3 fw-bold text-dark"><?= esc($title ?? 'Dashboard') ?></h1>
                        </div>
                        <div class="col-sm-6">
                            <ol class="breadcrumb float-sm-end text-sm mb-0">
                                <li class="breadcrumb-item"><a href="<?= base_url() ?>" class="text-decoration-none">Home</a></li>
                                <?php if (!empty($controller)): ?>
                                    <li class="breadcrumb-item active" aria-current="page"><?= esc($controller) ?></li>
                                <?php endif; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.content-header -->

            <!-- Main content -->
            <section class="content">
                <div class="container-fluid">
                    <!-- Flash Messages -->
                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-check-circle me-1"></i> <?= esc(session()->getFlashdata('success')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                            <i class="fas fa-exclamation-circle me-1"></i> <?= esc(session()->getFlashdata('error')) ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-12">
                            <!-- Area for dynamic content -->
                            <?= $this->renderSection("content") ?>
                            <!-- /Area for dynamic content -->
                        </div>
                    </div>
                </div>
            </section>
            <!-- /.content -->
        </div>
        <!-- /.content-wrapper -->

        <!-- Main Footer -->
        <footer class="main-footer">
            <div class="float-end d-none d-sm-inline">
                <b>Versi</b> 4.0 (CI4)
            </div>
            <strong>Copyright &copy; <?= date('Y') ?> <a href="<?= base_url() ?>" class="text-decoration-none">KOLAB.ID</a>.</strong> All rights reserved.
        </footer>

    </div>
    <!-- ./wrapper -->

    <!-- Global Script -->
    <?= $this->include('layout/globalscript') ?>

    <!-- PageScript -->
    <?= $this->renderSection("pageScript") ?>

</body>
</html>