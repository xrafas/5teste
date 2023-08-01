<?php include("topo.php");

require_once("../db_functions_ext.php");


//if ($_SESSION['p1'] == '1') {
?>

<script type="text/javascript" src="ajax.js"></script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>

<div class="content well pull-left span12">

	<div class="row">

		<div class="span12">

			<div class="box-adress">
				<ul>
					<li>
						<i class="fas fa-search"></i> <strong>Pesquisar OS</strong>
						<form name="frm_pesquisa" method="post" action="">


							Data
							<input type="date" id="data_calendario" name="data_calendario" value="<?php echo $_GET['data_calendario']; ?>">



							<?php
							$query_clientes = "SELECT Id, Nome FROM clientes GROUP BY Nome ORDER BY Nome ASC ";

							$result_clientes = $conn_ext->query($query_clientes);
							if (!$result_clientes) {
								die("Erro na consulta result_clientes: " . mysqli_error($conn_ext));
							}

							// Armazene os valores selecionados
							$selected_cliente = isset($_GET['cliente_filtro']) ? $_GET['cliente_filtro'] : "";
							$selected_tipo = isset($_GET['tipo_filtro']) ? $_GET['tipo_filtro'] : "";
							$selected_status = isset($_GET['status_filtro']) ? $_GET['status_filtro'] : "";

							// Obtenha o nome do cliente selecionado
							$selected_cliente_name = "";
							if ($selected_cliente != "") {
								foreach ($result_clientes as $cliente) {
									if ($cliente['Id'] == $selected_cliente) {
										$selected_cliente_name = $cliente['Nome'];
										break;
									}
								}
							}
							?>
							&nbsp;&nbsp;Clientes
							<select name="cliente_filtro" id="cliente_filtro" placeholder="cliente_filtro">
								<option value="<?= $selected_cliente ?>"><?= $selected_cliente_name ?></option>
								<?php foreach ($result_clientes as $cliente) : ?>
									<?php if ($cliente['Id'] != $selected_cliente) : ?>
										<option value="<?= $cliente['Id'] ?>"><?= $cliente['Nome'] ?></option>
									<?php endif; ?>
								<?php endforeach; ?>
							</select>

							&nbsp;&nbsp;Tipo
							<select name="tipo_filtro" id="tipo_filtro" placeholder="Tipo de visita">
								<?php if ($selected_tipo != "") : ?>
									<option value="<?= $selected_tipo ?>"><?= $selected_tipo ?></option>
								<?php endif; ?>

								<option value="CONTRATO" <?= $selected_tipo == "CONTRATO" ? "hidden" : "" ?>>CONTRATO</option>
								<option value="AVULSO" <?= $selected_tipo == "AVULSO" ? "hidden" : "" ?>>AVULSO</option>
							</select>

							&nbsp;&nbsp;Status
							<select name="status_filtro" id="status_filtro" placeholder="status">
								<?php if ($selected_status != "") : ?>
									<option value="<?= $selected_status ?>"><?= $selected_status ?></option>
								<?php endif; ?>

								<option value="ABERTO" <?= $selected_status == "ABERTO" ? "hidden" : "" ?>>ABERTO</option>
								<option value="EXECUTANDO" <?= $selected_status == "EXECUTANDO" ? "hidden" : "" ?>>EXECUTANDO</option>
								<option value="PAUSADO" <?= $selected_status == "PAUSADO" ? "hidden" : "" ?>>PAUSADO</option>
								<option value="FINALIZADO" <?= $selected_status == "FINALIZADO" ? "hidden" : "" ?>>FINALIZADO</option>
								<option value="CANCELADO" <?= $selected_status == "CANCELADO" ? "hidden" : "" ?>>CANCELADO</option>
							</select>




							<input type="button" id="b_ir" name="b_ir" value="IR" onclick="javascript:pesquisa_ir();">
							<br><br>

							<!--						

							<a class="btn btn-primary" href="fab_os_nova.php" role="button" style="display:block; width: 100px;"><span>Incluir</span></a>

							<a class="btn btn-primary" href="fab_os_nova.php" role="button" style="font-size: 18px; padding: 10px 20px;"><span>Incluir</span></a>

							<a class="btn btn-primary" href="fab_os_nova.php" role="button" style="min-width: 100px; min-height: 40px;"><span>Incluir</span></a>
								-->

							<!--<a class="btn btn-primary" target="_blank" href="visita_relatorio_impressao.php?data_calendario=<? //echo $_GET['data_calendario']; 
																																?>&medico_filtro=<? //echo $_GET['medico_filtro']; 
																																					?>&vendedor_filtro=<? //echo $_GET['vendedor_filtro']; 
																																										?>&tipo_filtro=<? //echo $_GET['tipo_filtro']; 
																																														?>" role=" button"><span>Incluir</span></a>
							-->
						</form>
						<!-- Adicionar div para envolver o botão Incluir -->
						<div style="position: relative; z-index: 100;">
							<a class="btn btn-primary" href="fab_os_nova.php" role="button" ><span>Incluir</span></a>
						</div>
					</li>

				</ul>

			</div>


			<hr>





			</ul>
		</div>

		<div class="box-form-container">

			<form action="" id="frm_pesquisa">

			</form>
			<!--Form-->

		</div>
		<!--BoxFormContainer-->

		<hr>


		<div class="box-table">

			<div id="Resultado">

				<?php

				// Verifica as permissões do usuário
				$isPrivilegedUser = ($_SESSION['p1'] == '1'); // Verifica se o usuário tem privilégios

				$exp = "";
				$limitFilter = " LIMIT 20";
				$lastEntries = true;

				if (!$isPrivilegedUser) {
					// Se não for um usuário privilegiado, ajusta as restrições para mostrar apenas as OS relacionadas e do dia atual
					$todayDate = date('Y-m-d');
					$limitFilter = "";
					$exp .= " AND scos.Data = '{$todayDate}' AND scosc.Id_Vendedor = " . intval($_SESSION['id_vendedor']);
				}

				// Se filtros foram aplicados
				if ((isset($_GET['data_calendario'])) || (isset($_GET['status_filtro'])) || (isset($_GET['cliente_filtro'])) || (isset($_GET['tipo_filtro']))) {
					$lastEntries = false;
					$limitFilter = "";

					if (!empty($_GET['data_calendario'])) {
						$exp .= " AND scos.Data = '" . $_GET['data_calendario'] . "' ";
					}

					if (!empty($_GET['status_filtro'])) {
						$exp .= " AND scos.Situacao = '" . $_GET['status_filtro'] . "' ";
					}

					if (!empty($_GET['cliente_filtro'])) {
						$exp .= " AND sc.Id_cliente = '" . $_GET['cliente_filtro'] . "' ";
					}

					if (!empty($_GET['tipo_filtro'])) {
						$exp .= " AND sc.Tipo = '" . $_GET['tipo_filtro'] . "' ";
					}
				}

				// Ajuste na consulta SQL para incluir a tabela 'srv_cont_os_servico_colaborador' com alias 'scosc'
				$query_count = "SELECT COUNT(*) AS Total FROM srv_cont_os scos LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id LEFT JOIN srv_cont_os_servico_colaborador scosc ON scos.Id = scosc.Id_OS WHERE 1=1 " . $exp;
				$result_count = $conn_ext->query($query_count);

				if (!$result_count) {
					die("Erro na consulta query_count: " . mysqli_error($conn_ext));
				}




				$Total = $result_count->fetch_assoc();

				?>
				<div class="up-box-form-container">
					<ul>
						<label><b>
								<?php
								if ($Total['Total'] > 0) {
									if ($isPrivilegedUser) {
										if ($lastEntries) {
											echo "Últimas 20 OS:";
										} else {
											echo "Quantidade de OS: " . $Total['Total'];
										}
									} else {
										echo "OS agendas para hoje:";
									}
								}
								?>
							</b></label>

					</ul>

					<?php
					//$sql = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, c.Nome AS NomeCliente FROM srv_cont_os scos LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id LEFT JOIN clientes c ON sc.Id_cliente = c.Id WHERE 1=1 " . $exp . " ORDER BY scos.Id DESC" . $limitFilter;
					//$sql = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, c.Nome AS NomeCliente FROM srv_cont_os scos LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id LEFT JOIN clientes c ON sc.Id_cliente = c.Id LEFT JOIN srv_cont_os_servico_colaborador scosc ON scos.Id = scosc.Id_OS WHERE 1=1 " . $exp . " ORDER BY scos.Id DESC" . $limitFilter;
					$sql = "SELECT scos.*, sc.Id AS IdContrato, sc.Tipo AS TipoOs, c.Nome AS NomeCliente FROM srv_cont_os scos LEFT JOIN srv_cont sc ON scos.id_SRV = sc.Id LEFT JOIN clientes c ON sc.Id_cliente = c.Id LEFT JOIN srv_cont_os_servico_colaborador scosc ON scos.Id = scosc.Id_OS WHERE 1=1 " . $exp . " ORDER BY scos.Id DESC" . $limitFilter;

					$result = $conn_ext->query($sql);
					if (!$result) {
						die("Erro na consulta result: " . mysqli_error($conn_ext));
					}
					?>

					<div class="box-form-container">
						<?php if ($Total['Total'] > 0) : ?>
							<?php while ($dash = $result->fetch_assoc()) : ?>
								<ul>
									<li>
										<label><i>Número da OS</i></label>
										<?php echo $dash['Id']; ?>
									</li>
									<li>
										<label><i>Número do Contrato</i></label>
										<?php echo $dash['IdContrato']; ?>
									</li>
									<li>
										<label><i>Nome do Cliente</i></label>
										<?php echo $dash['NomeCliente']; ?>
									</li>

									<li>
										<label><i>Tipo</i></label>
										<?php echo $dash['TipoOs']; ?>
									</li>
									<li>
										<label><i>Data</i></label>
										<?php echo date("d/m/Y", strtotime($dash['Data'])); ?>
									</li>
									<li>
										<label><i>Situação</i></label>
										<?php echo $dash['Situacao']; ?>
									</li>
									<li>
										<label><i>Usuário Execução</i></label>

										<?php
										$tipo = $dash['Execucao_Usuario'];
										if (empty($tipo)) {
											$tipo = "Não indicado";
										}
										echo $tipo;
										?>

									</li>

									<li>
										<a href="fab_os_detalhes.php?id_os=<?php echo $dash['Id']; ?>"><label><i>Detalhes</i></label>
											<i class="fas fa-info-circle"></i></a>
									</li>
								</ul>
								<hr>
							<?php endwhile; ?>

						<?php else : ?>
							<div class="no-orders-message">
								<ul>
									<li>Não foram encontradas ordens de serviço.</li>
								</ul>
							</div>
						<?php endif; ?>



					</div> <!-- container -->


				</div>
				<!--Resultado-->

			</div>
			<!--BoxTable-->

		</div>
		<!--Span-->

	</div>
	<!--Row-->

</div>
<!--Content-->

<div>


	<div>




		<div id="Resultado">

			</hr>
			</body>

			</html>

		</div>
	</div>
</div>
</div>
</div>

<?php include("base.php");
//} 
?>


<script>
	function pesquisa_ir() {

		var aux = '';


		if (document.frm_pesquisa.data_calendario.value !== '') {
			aux += 'data_calendario=' + document.frm_pesquisa.data_calendario.value + '&';
		}
		if (document.frm_pesquisa.cliente_filtro.value !== '') {
			aux += 'cliente_filtro=' + document.frm_pesquisa.cliente_filtro.value + '&';
		}
		if (document.frm_pesquisa.tipo_filtro.value !== '') {
			aux += 'tipo_filtro=' + document.frm_pesquisa.tipo_filtro.value + '&';
		}
		if (document.frm_pesquisa.status_filtro.value !== '') {
			aux += 'status_filtro=' + document.frm_pesquisa.status_filtro.value + '&';
		}

		url = 'fab_os.php?' + aux;
		//window.alert(url);
		location.href = url;
	}
</script>
<style>
	.no-orders-message {
		margin: 0;
		padding: 0;
		clear: both;
		/* Isso garante que a div não vai sobrepor ou ser sobreposta por elementos flutuantes com o botão Incluir */
	}
</style>