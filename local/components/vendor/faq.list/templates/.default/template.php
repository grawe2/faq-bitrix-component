<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>

<?php if (!empty($arResult['ITEMS'])): ?>
	<div class="faq">
		<?php foreach ($arResult['ITEMS'] as $arItem): ?>
			<div class="faq__item">
				<div class="faq__question">
					<?= htmlspecialchars($arItem['NAME']) ?> <i class="faq__icon"></i>
				</div>
				<div class="faq__answer">
					<div class="faq__wr">
						<?= $arItem['PREVIEW_TEXT'] ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
<?php endif; ?>