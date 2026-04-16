<?php
use Modules\Main\Auth;

$showAdminSidebar = Auth::getInstance()->isAdmin();
?>
<?php if ($showAdminSidebar): ?>
			</div>
		</main>
	</div>
<?php else: ?>
	</main>
<?php endif; ?>
<script src="/Templates/Default/js/bootstrap.bundle.min.js"></script>
</body>
</html>
