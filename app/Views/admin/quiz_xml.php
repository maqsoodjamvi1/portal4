<?= $this->extend('layouts/admin_template') ?>
<?= $this->section('content') ?>

<?xml version="1.0" encoding="UTF-8"?>
<questions>
	<category>
		<thumb name="Subcategory">assets/item_thumb_0.svg</thumb>
	</category>
	
	<item>
		<category>Subcategory</category>
		<landscape>
			<question type="text" align="center" top="8"><![CDATA[Drag the right puzzle:]]></question>
			<answers correctAnswer="" drag="true">
				<answer top="40" width="10" height="11" left="70" type="image">assets/questions/bear_drag.svg</answer>
				<answer top="84" type="text" submit="true" left="32" width="35"><![CDATA[Done]]></answer>
			</answers>
			<groups>
				<group correctAnswer="" dropWidth="10" dropHeight="18" dropLeft="19.2" dropTop="55" dropMax="1" top="55" left="19.4" width="10" height="10" type="image" align="center" dropOffLeft=".1" dropOffTop=".1">assets/questions/bear_drop_01.svg</group>
				<group correctAnswer="1" dropWidth="10" dropHeight="18" dropLeft="44.6" dropTop="55" dropMax="1" top="55" left="44.7" width="10" height="10" type="image" align="center" dropOffLeft=".1" dropOffTop=".1">assets/questions/bear_drop_02.svg</group>
			</groups>
			<background top="19" left="10" width="50">assets/questions/bear.svg</background>
		</landscape>
		
		<portrait>
			<question type="text" align="center" top="12" fontSize="30" lineHeight="40"><![CDATA[Drag the right puzzle:]]></question>
			<answers correctAnswer="" drag="true">
				<answer top="65" width="18" height="10" left="41" type="image">assets/questions/bear_drag.svg</answer>
				<answer top="82" type="text" submit="true" left="32" width="35" fontSize="20" lineHeight="20" height="8" align="center"><![CDATA[Done]]></answer>
			</answers>
			<groups>
				<group correctAnswer="" dropWidth="18" dropHeight="11" dropLeft="22" dropTop="46.1" dropMax="1" top="46.1" left="22" width="18" height="10" type="image" dropOffLeft=".1" dropOffTop=".1">assets/questions/bear_drop_01.svg</group>
				<group correctAnswer="1" dropWidth="18" dropHeight="11" dropLeft="44" dropTop="46.1" dropMax="1" top="46.1" left="44.7" width="18" height="10" type="image" dropOffLeft=".1" dropOffTop=".1">assets/questions/bear_drop_02.svg</group>
			</groups>
			<background top="24" left="5" width="90">assets/questions/bear.svg</background>
		</portrait>
	</item>
</questions>

<?= $this->endSection() ?>