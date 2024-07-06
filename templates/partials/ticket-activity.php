

<div class="col-md-8  ">
    <h5>Last Ticket Activity</h5>
	<div class="table-wrapper box-custom p-3 es_section_scroll">
		<table class="recent-activities-tbl">
			<tbody>
			<?php foreach($report['ticket_activities'] as $activity){ ?>
				<tr>
					<td>
						<strong><?= $activity['user_name'] ?></strong> has sent a <a href="#">response</a> to the ticket <a href="https://earthsavers.org/wp-admin/post.php?post=<?=  $activity['id'] ?>&action=edit"><?=  $activity['ticket_name'] ?> (#<?=  $activity['id'] ?>)</a>
						<span class="last-act-time"><?= $activity['time_ago'] ?></span>
					</td>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	</div>
</div>
