<?php
require_once 'conexion.php';
include 'includes/header.php';

$usuarios = $pdo->query("SELECT * FROM usuarios");

include 'includes/sidebar.php';
?>

            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2>Gestión de Usuarios</h2>
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalUsuario">
                    <i class="bi bi-plus-circle"></i> Nuevo Usuario
                </button>
            </div>

            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>Usuario</th>
                        <th>Correo</th>
                        <th>Celular</th>
                        <th class="table-actions">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($usuario = $usuarios->fetch()): ?>
                    <tr>
                        <td><?= $usuario['nombre_completo'] ?></td>
                        <td><?= $usuario['usuario'] ?></td>
                        <td><?= $usuario['correo'] ?></td>
                        <td><?= $usuario['celular'] ?></td>
                        <td>
                            <button class="btn btn-sm btn-primary">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-sm btn-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

           
            <div class="modal fade" id="modalUsuario">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title">Nuevo Usuario</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                        </div>
                        <form action="guardar_usuario.php" method="POST">
                            <div class="modal-body">
                                <div class="row g-3">
                                    <div class="col-12">
                                        <label class="form-label">Nombre Completo</label>
                                        <input type="text" class="form-control" name="nombre_completo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Usuario</label>
                                        <input type="text" class="form-control" name="usuario" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Contraseña</label>
                                        <input type="password" class="form-control" name="contraseña" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Correo</label>
                                        <input type="email" class="form-control" name="correo" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Celular</label>
                                        <input type="tel" class="form-control" name="celular" required>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                <button type="submit" class="btn btn-success">Guardar</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>