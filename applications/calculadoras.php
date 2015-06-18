<?php
/**********************************************************************
 Copyright (C) FrontAccounting, LLC.
Released under the terms of the GNU General Public License, GPL,
as published by the Free Software Foundation, either version 3
of the License, or (at your option) any later version.
This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
See the License here <http://www.gnu.org/licenses/gpl-3.0.html>.
***********************************************************************/
class calculadoras_app extends application
{
	function calculadoras_app()
	{
		$this->application("CM", _($this->help_context = "&Calculadoras"));

		$this->add_module(_("Transactions"));
		/**$this->add_lapp_function(0, _("Despliega calculadoras"),
				"calculadoras/despliega.php?",'SA_OPEN',  MENU_TRANSACTION);**/

		$this->add_lapp_function(0, "Calculadora de Arco",
				"calculadoras/calcArco.php",'SA_OPEN',  MENU_TRANSACTION);
        /**
		$this->add_module(_("Maintenance"));
		$this->add_lapp_function(1, _("Creación de calculadoras"),
				"calculadoras/creacion.php?",'SA_OPEN',  MENU_MAINTENANCE);
		$this->add_rapp_function(1, _("Visualización de calculadoras"),
				"calculadoras/visualizacion.php?",'SA_OPEN',  MENU_MAINTENANCE);
		**/
		
		$this->add_extensions();
	}
}
?>