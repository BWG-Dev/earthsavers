<nav aria-label="beg-pagination" class="beg-pagination">
	<ul class="pagination">
		<?php

		if($page != 1 && $count > 0){
			echo '<li class="page-item"><a class="page-link" href="https://earthsavers.org/wp-admin/admin.php?page=es-users&cur_page=' . ($page - 1).'">Previous</a></li>';
		}
		for($i = 0; $i < $pages; $i++){
			$active = $i + 1 == $page ? 'active' : '';
			?>
			<li class="page-item <?= $active ?>"><a class="page-link" href="https://earthsavers.org/wp-admin/admin.php?page=es-users&cur_page=<?= ($i + 1); ?>"><?= $i + 1 ?></a></li>
			<?php
		}
		if($page != $pages && $count > 0){
			echo '<li class="page-item"><a class="page-link" href="https://earthsavers.org/wp-admin/admin.php?page=es-users&cur_page=' . ($page + 1).'">Next</a></li>';
		}

		?>
	</ul>
</nav>
