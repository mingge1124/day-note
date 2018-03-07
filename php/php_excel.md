## PHP_EXCEL 使用
> 包来源：[https://github.com/PHPOffice/PHPExcel](https://github.com/PHPOffice/PHPExcel)   
> 包简介：PHPExcel last version, 1.8.1, was released in 2015. The project is no longer maintained and should not be used anymore.
All users should migrate to its direct successor PhpSpreadsheet, or another alternative.In a few months, the GitHub project will be marked as archived, so everything will be read-only.     

Excel 基本了解
---
1. 每个 excel 文件含有多个 sheet (表)     
2. 每个 sheet 含有多个 cell (单元格)，含有 title (标题)、样式、...等属性
3. 每个 sheet 含有多个 columnDimension (单元格)		
4. 每个 cell 含有多个 column (列) 和 row （行），含有 value （值）、format (格式) 、样式 、...等属性				


基本使用
---
实例化一个excel对象
```
$excel = new PHPExcel();
```

设置excel属性
```
$excel->getProperties()->setCreator("Maarten Balliauw")
					->setLastModifiedBy("Maarten Balliauw")
					->setTitle("PHPExcel Test Document")
					->setSubject("PHPExcel Test Document")
					->setDescription("Test document for PHPExcel, generated using PHP classes.")
					->setKeywords("office PHPExcel php")
					->setCategory("Test result file");
```

设置指定操作sheet，默认index从0开始
```
$excel->setActiveSheetIndex(0);
```


获取当前指向的sheet
```
$sheet = $excel->getActiveSheet();
```        

设置sheet属性
```
$sheet->setTitle('表标题');    
```


获取行，列对象；rowNumber从1开始，columnNumber从A开始
```
$sheet->getRowDimension(rowNumber);
$sheet->getColumnDimension(columnNumber);
```


设置行高，列宽，自动宽度
```
$sheet->getRowDimension(1)->setRowHeight(10.00);
$sheet->getColumnDimension('A')->setColumnWidth(10.00);	
$sheet->getColumnDimension('B')->setAutoSize(true);
```

获取单元格格式操作对象，参数为坐标
```
$cellStyle = $sheet->getStyle('A5');
```

设置单元格数字格式
```
$cellStyle->getNumerFormat()
		  ->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_NUMBER);
```		  

设置单元格对齐方式，水平向右，垂直居中
```
$alignment = $cellStyle->getAlignment();
$alignment->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);  
$alignment->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);  
```

设置单元格字体  
```
$font = $cellStyle->getFont();  
$font->setName('Courier New');  
$font->setSize(10);  
$font->setBold(true);  
$font->setUnderline(PHPExcel_Style_Font::UNDERLINE_SINGLE);  
$font->getColor()->setARGB('FF999999'); 
```

设置单元格边框  
```
$border = $cellStyle->getBorders();  
$border->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getTop()->getColor()->setARGB('FFFF0000'); // color  
$border->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
$border->getRight()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);  
```


设置填充颜色  
```
$fill = $cellStyle->getFill();  
$fill->setFillType(PHPExcel_Style_Fill::FILL_SOLID);  
$fill->getStartColor()->setARGB('FFEEEEEE');  
```



