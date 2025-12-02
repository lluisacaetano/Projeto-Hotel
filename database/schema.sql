-- ===============================
-- RESET CONFIGS
-- ===============================
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- ===============================
-- CREATE DATABASE
-- ===============================
DROP DATABASE IF EXISTS `mydb`;
CREATE DATABASE `mydb` DEFAULT CHARACTER SET utf8;
USE `mydb`;

-- ===============================
-- TABELA: ENDERECO
-- ===============================
CREATE TABLE `endereco` (
  `id_endereco` INT NOT NULL AUTO_INCREMENT,
  `logradouro` VARCHAR(45),
  `numero` INT,
  `bairro` VARCHAR(45),
  `cidade` VARCHAR(45),
  `estado` VARCHAR(45),
  `pais` VARCHAR(45),
  `cep` VARCHAR(45),
  PRIMARY KEY (`id_endereco`)
) ENGINE=InnoDB;

-- ===============================
-- TABELA: PESSOA
-- ===============================
CREATE TABLE `pessoa` (
  `id_pessoa` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45),
  `sexo` VARCHAR(45),
  `data_nascimento` DATE,
  `documento` VARCHAR(45),
  `telefone` VARCHAR(45),
  `email` VARCHAR(45),
  `tipo_pessoa` VARCHAR(45),
  `data_criacao` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `endereco_id_endereco` INT NOT NULL,
  PRIMARY KEY (`id_pessoa`),
  INDEX `fk_pessoa_endereco_idx` (`endereco_id_endereco` ASC),
  CONSTRAINT `fk_pessoa_endereco`
      FOREIGN KEY (`endereco_id_endereco`)
      REFERENCES `endereco` (`id_endereco`)
      ON DELETE NO ACTION
      ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: HOSPEDE (subtipo de pessoa)
-- ===============================
CREATE TABLE `hospede` (
  `id_pessoa` INT NOT NULL,
  `preferencias` VARCHAR(45),
  `historico` VARCHAR(45),
  PRIMARY KEY (`id_pessoa`),
  CONSTRAINT `fk_hospede_pessoa1`
    FOREIGN KEY (`id_pessoa`)
    REFERENCES `pessoa` (`id_pessoa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: FUNCIONARIO (subtipo de pessoa)
-- ===============================
CREATE TABLE `funcionario` (
  `id_pessoa` INT NOT NULL,
  `cargo` VARCHAR(45),
  `salario` DECIMAL(10,2),
  `data_contratacao` DATE,
  `numero_ctps` INT,
  `turno` VARCHAR(45),
  PRIMARY KEY (`id_pessoa`),
  CONSTRAINT `fk_funcionario_pessoa1`
    FOREIGN KEY (`id_pessoa`)
    REFERENCES `pessoa` (`id_pessoa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: QUARTO
-- ===============================
CREATE TABLE `quarto` (
  `id_quarto` INT NOT NULL AUTO_INCREMENT,
  `status` VARCHAR(45),
  `numero` INT,
  `andar` INT,
  `descricao` VARCHAR(45),
  `valor_diaria` DECIMAL(10,2),
  `capacidade_maxima` INT UNSIGNED,
  `tipo_quarto` VARCHAR(45),
  PRIMARY KEY (`id_quarto`)
) ENGINE=InnoDB;

-- ===============================
-- TABELA: RESERVA
-- ===============================
CREATE TABLE `reserva` (
  `idreserva` INT NOT NULL AUTO_INCREMENT,
  `valor_reserva` DECIMAL(10,2),
  `data_reserva` DATE,
  `data_checkin_previsto` DATE,
  `data_checkout_previsto` DATE,
  `status` VARCHAR(45),
  `id_funcionario` INT NOT NULL,
  `id_hospede` INT NOT NULL,
  `id_quarto` INT NOT NULL,
  PRIMARY KEY (`idreserva`),
  CONSTRAINT `fk_reserva_funcionario1`
    FOREIGN KEY (`id_funcionario`)
    REFERENCES `funcionario` (`id_pessoa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reserva_hospede1`
    FOREIGN KEY (`id_hospede`)
    REFERENCES `hospede` (`id_pessoa`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reserva_quarto1`
    FOREIGN KEY (`id_quarto`)
    REFERENCES `quarto` (`id_quarto`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: QUARTO_LUXO
-- ===============================
CREATE TABLE `quarto_luxo` (
  `quarto_id_quarto` INT NOT NULL,
  `possui_hidromassagem` VARCHAR(45),
  `possui_vista_mar` VARCHAR(45),
  PRIMARY KEY (`quarto_id_quarto`),
  CONSTRAINT `fk_quarto_luxo_quarto1`
    FOREIGN KEY (`quarto_id_quarto`)
    REFERENCES `quarto` (`id_quarto`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: PAGAMENTO
-- ===============================
CREATE TABLE `pagamento` (
  `id_pagamento` INT NOT NULL AUTO_INCREMENT,
  `data_pagamento` DATE,
  `valor_total` DECIMAL(10,2),
  `metodo_pagamento` VARCHAR(45),
  `reserva_idreserva` INT NOT NULL,
  PRIMARY KEY (`id_pagamento`),
  CONSTRAINT `fk_pagamento_reserva1`
    FOREIGN KEY (`reserva_idreserva`)
    REFERENCES `reserva` (`idreserva`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: CONSUMO
-- ===============================
CREATE TABLE `consumo` (
  `id_consumo` INT NOT NULL AUTO_INCREMENT,
  `data_consumo` DATE,
  `valor_consumacao` DECIMAL(10,2),
  `reserva_idreserva` INT NOT NULL,
  PRIMARY KEY (`id_consumo`),
  CONSTRAINT `fk_consumo_reserva1`
    FOREIGN KEY (`reserva_idreserva`)
    REFERENCES `reserva` (`idreserva`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- ===============================
-- TABELA: ITEM
-- ===============================
CREATE TABLE `item` (
  `id_item` INT NOT NULL AUTO_INCREMENT,
  `nome` VARCHAR(45),
  `valor` DECIMAL(10,2),
  `descricao` VARCHAR(45),
  PRIMARY KEY (`id_item`)
) ENGINE=InnoDB;

-- ===============================
-- TABELA: ITEM_HAS_CONSUMO
-- ===============================
CREATE TABLE `item_has_consumo` (
  `item_id_item` INT NOT NULL,
  `consumo_id_consumo` INT NOT NULL,
  `quantidade` INT,
  PRIMARY KEY (`item_id_item`, `consumo_id_consumo`),
  CONSTRAINT `fk_item_has_consumo_item1`
    FOREIGN KEY (`item_id_item`)
    REFERENCES `item` (`id_item`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_item_has_consumo_consumo1`
    FOREIGN KEY (`consumo_id_consumo`)
    REFERENCES `consumo` (`id_consumo`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION
) ENGINE=InnoDB;

-- Restoring configs
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
