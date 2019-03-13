<?php
namespace tsframe\module;

interface PaginatorInterface {

	/**
	 * Получить размер всех данных в базе
	 * @return int
	 */
	public static function getDataSize(): int ;

	/**
	 * Получить "срез" данных на определенной странице
	 * @param  int    $offset Отступ от начальной строки
	 * @param  int    $limit  Максимальное количество строк
	 * @return arrays
	 */
	public static function getDataSlice(int $offset, int $limit): array ;
}